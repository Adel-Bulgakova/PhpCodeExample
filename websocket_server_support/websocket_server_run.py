import time, signal, sys, ssl, json, hashlib, smtplib, re, datetime
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

from websocket_server import WebSocket, WebSocketServer, SSLWebSocketServer
from optparse import OptionParser

# Check python version
VER = sys.version_info[0]
# print('python_VER: ' + str(VER))
try:
    from urllib.request import Request, urlopen  # Python 3
except:
    from urllib2 import Request, urlopen  # Python 2

clients_pull = []
support_admins = []
accepted_chats = []
waiting_chats = []

# ===== Don't change =====
site_auth_login = ''
site_auth_pass = ''
# ===== Don't change =====

api_url_path = '/api/v1/'


class WsUserWrapper():
    def __init__(self, client, user_id, user_display_name, user_image_url, user_oauth_signature, user_session_id):
        self.client = client
        self.user_id = user_id
        self.user_display_name = user_display_name
        self.user_image_url = user_image_url
        self.user_oauth_signature = user_oauth_signature
        self.user_session_id = user_session_id


class WsAdminWrapper():
    def __init__(self, client, admin_id, admin_display_name, admin_image_url, admin_oauth_signature, admin_page_session_id):
        self.client = client
        self.admin_id = admin_id
        self.admin_display_name = admin_display_name
        self.admin_image_url = admin_image_url
        self.admin_oauth_signature = admin_oauth_signature
        self.admin_page_session_id = admin_page_session_id


class AcceptedChat():
    def __init__(self, chat_id, ws_admin, ws_user):
        self.chat_id = chat_id
        self.ws_admin = ws_admin
        self.ws_user = ws_user


class WaitingChat():
    def __init__(self, chat_id, ws_user, messages, created_timestamp):
        self.chat_id = chat_id
        self.ws_user = ws_user
        self.messages = messages
        self.created_timestamp = created_timestamp

    def update_messages(self, message):
        self.messages.update(message)


class SupportChat(WebSocket):
    def handleMessage(self):
        json_data = json.loads(self.data)
        type = json_data['type']
        client_status = json_data['client_status']
        client_id = json_data['client_id']

        if ('lang' in json_data) and (json_data['lang'] in ['ru', 'en']):
            lang = json_data['lang']
        else:
            lang = 'ru'

        if type == 'system_message':
            if client_status == 'admin':
                # Create admin signature
                hash_object = hashlib.sha1(site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(client_id).encode('utf-8') + 'admin'.encode('utf-8'))
                oauth_signature = hash_object.hexdigest()

                # Get admin connect access token
                request_query_connect = 'support_service/support_admin_connect_state'
                access_token_connect_res = get_access_token(request_query=request_query_connect, oauth_signature=oauth_signature)
                connect_access_token_status = access_token_connect_res['status']

                # Get admin data access token
                request_query_admin_data = 'support_service/admin'
                access_token_admin_data_res = get_access_token(request_query=request_query_admin_data,
                                                               oauth_signature=oauth_signature)
                admin_data_access_token_status = access_token_admin_data_res['status']

                if connect_access_token_status == 'OK' and admin_data_access_token_status == 'OK':
                    connect_access_token = access_token_connect_res['access_token']
                    admin_data_access_token = access_token_admin_data_res['access_token']

                    # Get admin connect result
                    connect_state_data = json.dumps({'client_connect_state': 'connect'}).encode('utf8')
                    connect_state_data_url = api_url_path + request_query_connect
                    connect_state_res = get_data_from_url(request_query=request_query_connect, access_token=connect_access_token, auth_signature=oauth_signature, data=connect_state_data, url=connect_state_data_url, session_id='')
                    connect_state_status = connect_state_res['status']

                    if connect_state_status == 'OK':
                        admin_page_session_id = connect_state_res['admin_session_id']

                        # Get admin data
                        admin_data_url = api_url_path + request_query_admin_data + '/' + str(client_id)
                        admin_data = get_data_from_url(request_query=request_query_admin_data,
                                                           access_token=admin_data_access_token,
                                                           auth_signature=oauth_signature, data='',
                                                           url=admin_data_url, session_id='')
                        admin_data_status = admin_data['status']

                        if admin_data_status == 'OK':
                            admin_display_name = admin_data['data']['display_name']
                            admin_image_url = admin_data['data']['profile_image']

                            # Create admin ws
                            ws_admin = WsAdminWrapper(self, admin_id=client_id, admin_display_name=admin_display_name,
                                                      admin_image_url=admin_image_url, admin_oauth_signature=oauth_signature,
                                                      admin_page_session_id=admin_page_session_id)
                            support_admins.append(ws_admin)
                            clients_pull.remove(self)

                            response_data = {}
                            update_waiting_users_list_for_admins(response_data)

                            write_to_log(u'admin connect: OK client_id - ' + str(client_id))

            if client_status == 'user':

                if 'device_uuid' in json_data:
                    device_uuid = json_data['device_uuid']
                    session_id = json_data['session_id']
                    # Create user/device signature
                    hash_object = hashlib.sha1(client_id.encode('utf-8') + device_uuid.encode('utf-8'))
                    oauth_signature = hash_object.hexdigest()
                else:
                    session_id = json_data['sid']
                    # Create user signature
                    hash_object = hashlib.sha1(site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(client_id).encode('utf-8'))
                    oauth_signature = hash_object.hexdigest()

                # Get admin connect to chat access token
                request_query_connect = 'support_service/client_connect_state'
                access_token_connect_res = get_access_token(request_query=request_query_connect,
                                                            oauth_signature=oauth_signature)
                connect_access_token_status = access_token_connect_res['status']

                # Get user data access token
                request_query_user_data = 'users/user'
                access_token_user_data_res = get_access_token(request_query=request_query_user_data, oauth_signature=oauth_signature)
                user_data_access_token_status = access_token_user_data_res['status']

                if connect_access_token_status == 'OK' and user_data_access_token_status == 'OK':
                    connect_access_token = access_token_connect_res['access_token']
                    user_data_access_token = access_token_user_data_res['access_token']

                    # Get admin connect result
                    connect_state_data = json.dumps(
                        {'client_connect_state': 'connect', 'client_status': 'user',
                         'client_id': str(client_id)}).encode('utf8')
                    connect_state_url = api_url_path + request_query_connect
                    connect_state_res = get_data_from_url(request_query=request_query_connect,
                                                              access_token=connect_access_token,
                                                              auth_signature=oauth_signature, data=connect_state_data,
                                                              url=connect_state_url, session_id=session_id)
                    connect_state_status = connect_state_res['status']

                    if connect_state_status == 'OK':
                        chat_id = connect_state_res['chat_id']

                        user_data_url = api_url_path + request_query_user_data + '/' + str(client_id)
                        user_data = get_data_from_url(request_query=request_query_user_data,
                                                          access_token=user_data_access_token,
                                                          auth_signature=oauth_signature, data='',
                                                          url=user_data_url,
                                                          session_id=session_id)
                        user_data_status = user_data['status']
                        if user_data_status == 'OK':
                            user_display_name = user_data['data']['display_name']
                            user_image_url = user_data['data']['profile_image']

                            write_to_log(u'user connect: OK client_id - ' + str(client_id) + u', chat_id: ' + str(chat_id))

                            # Create admin ws
                            ws_user = WsUserWrapper(self, user_id=client_id, user_display_name=user_display_name,
                                                        user_image_url=user_image_url, user_oauth_signature=oauth_signature,
                                                        user_session_id=session_id)
                            # Add opened chat to waiting chats list
                            waiting_chat = WaitingChat(chat_id, ws_user, {}, int(time.time()))
                            waiting_chats.append(waiting_chat)
                            clients_pull.remove(self)

                            send_email_to_admins(email_type='new_chat', chat_id=chat_id, oauth_signature=oauth_signature, session_id=session_id)

                            response_data = {}
                            response_data['type'] = type
                            response_data['chat_id'] = chat_id
                            response_data['online_admins'] = len(support_admins)
                            waiting_chat.ws_user.client.sendMessage(u'' + json.dumps(response_data))

                            update_waiting_users_list_for_admins(response_data)
                        else:
                            write_to_log(u'user connect: ERROR client_id - ' + str(client_id))

        if type == 'accept_chat':
            client_id = json_data['client_id']
            chat_id = json_data['chat_id']

            for ws_admin in support_admins:
                if ws_admin.client == self:
                    admin_oauth_signature = ws_admin.admin_oauth_signature
                    admin_page_session_id = ws_admin.admin_page_session_id
                    admin_display_name = ws_admin.admin_display_name
                    admin_image_url = ws_admin.admin_image_url

                    # Get admin connect to chat access token
                    request_query_connect_to_chat = 'support_service/client_connect_state'
                    access_token_connect_to_chat_res = get_access_token(request_query=request_query_connect_to_chat,
                                                                   oauth_signature=admin_oauth_signature)
                    connect_to_chat_access_token_status = access_token_connect_to_chat_res['status']

                    if connect_to_chat_access_token_status == 'OK':
                        connect_to_chat_access_token = access_token_connect_to_chat_res['access_token']

                        connect_admin_to_chat_data = json.dumps(
                            {'client_connect_state': 'connect', 'client_status': 'admin', 'client_id': str(client_id),
                             'chat_id': str(chat_id)}).encode(
                            'utf8')
                        connect_admin_to_chat_url = api_url_path + request_query_connect_to_chat
                        connect_admin_to_chat_query_data = get_data_from_url(request_query=request_query_connect_to_chat,
                                                          access_token=connect_to_chat_access_token,
                                                          auth_signature=admin_oauth_signature, data=connect_admin_to_chat_data,
                                                          url=connect_admin_to_chat_url, session_id='')

                        connect_admin_to_chat_status = connect_admin_to_chat_query_data['status']
                        if connect_admin_to_chat_status == 'OK':

                            # Delete chat from waiting chats list, add chat to accepted chats list
                            for waiting_chat in waiting_chats:
                                waiting_chat_id = waiting_chat.chat_id
                                ws_user = waiting_chat.ws_user
                                user_id = waiting_chat.ws_user.user_id
                                user_display_name = waiting_chat.ws_user.user_display_name
                                user_image_url = waiting_chat.ws_user.user_image_url
                                existing_messages = waiting_chat.messages

                                if int(waiting_chat_id) == int(chat_id):
                                    response_data = {}
                                    response_data['type'] = 'chat_accepted'

                                    # Send message to admin
                                    response_data['chat_id'] = chat_id
                                    response_data['user_id'] = user_id
                                    response_data['user_display_name'] = user_display_name
                                    response_data['user_image_url'] = user_image_url
                                    response_data['existing_messages'] = existing_messages
                                    ws_admin.client.sendMessage(u'' + json.dumps(response_data))

                                    # Send message to user
                                    response_data['chat_accepted_by'] = client_id
                                    response_data['admin_display_name'] = admin_display_name
                                    response_data['admin_image_url'] = admin_image_url
                                    ws_user.client.sendMessage(u'' + json.dumps(response_data))

                                    accepted_chat = AcceptedChat(chat_id, ws_admin, ws_user)
                                    accepted_chats.append(accepted_chat)
                                    waiting_chats.remove(waiting_chat)

                            response_data = {}
                            update_waiting_users_list_for_admins(response_data)

                            write_to_log(u'accept_chat: OK client_id - ' + str(client_id) + u' chat_id - ' + str(chat_id))

                        else:
                            write_to_log(u'accept_chat' + connect_admin_to_chat_query_data['status'])

        if type == 'close_chat':
            client_id = json_data['client_id']
            chat_id = json_data['chat_id']

        if type == 'message':
            chat_id = json_data['chat_id']
            message = json_data['message']

            add_message_request_query = 'support_service/add_message'

            for waiting_chat in waiting_chats:
                if int(waiting_chat.chat_id) == int(chat_id) and waiting_chat.ws_user.client == self:
                    timestamp = int(time.time())
                    message_data = {timestamp: message}
                    waiting_chat.update_messages(message_data)

                    ws_user = waiting_chat.ws_user.client
                    user_oauth_signature = waiting_chat.ws_user.user_oauth_signature
                    user_session_id = waiting_chat.ws_user.user_session_id
                    user_display_name = waiting_chat.ws_user.user_display_name
                    user_image_url = waiting_chat.ws_user.user_image_url

                    send_email_to_admins(email_type='new_message', chat_id=chat_id, oauth_signature=user_oauth_signature, session_id=user_session_id)

                    add_message_access_token_res = get_access_token(request_query=add_message_request_query,
                                                            oauth_signature=user_oauth_signature)
                    add_message_access_token_status = add_message_access_token_res['status']
                    if add_message_access_token_status == 'OK':
                        add_message_access_token = add_message_access_token_res['access_token']

                        add_message_data = json.dumps(
                            {'message': message, 'chat_id': str(chat_id)}).encode('utf8')
                        add_message_url = api_url_path + add_message_request_query
                        add_message_query_data = get_data_from_url(request_query=add_message_request_query,
                                                                       access_token=add_message_access_token,
                                                                       auth_signature=user_oauth_signature,
                                                                       data=add_message_data,
                                                                       url=add_message_url, session_id=user_session_id)

                        if add_message_query_data['status'] == 'OK':
                            response_data = {}
                            response_data['type'] = 'message'
                            response_data['chat_id'] = chat_id
                            response_data['message'] = message
                            response_data['client_status'] = client_status
                            response_data['client_display_name'] = user_display_name
                            response_data['client_image_url'] = user_image_url
                            ws_user.sendMessage(u'' + json.dumps(response_data))
                            update_waiting_users_list_for_admins({})

            for accepted_chat in accepted_chats:
                if int(accepted_chat.chat_id) == int(chat_id):
                    ws_user = accepted_chat.ws_user.client
                    ws_admin = accepted_chat.ws_admin.client
                    if client_status == 'admin':
                        oauth_signature = accepted_chat.ws_admin.admin_oauth_signature
                        session_id = ''
                        client_display_name = accepted_chat.ws_admin.admin_display_name
                        client_image_url = accepted_chat.ws_admin.admin_image_url
                    if client_status == 'user':
                        oauth_signature = accepted_chat.ws_user.user_oauth_signature
                        session_id = accepted_chat.ws_user.user_session_id
                        client_display_name = accepted_chat.ws_user.user_display_name
                        client_image_url = accepted_chat.ws_user.user_image_url

                    add_message_access_token_res = get_access_token(request_query=add_message_request_query,
                                                                        oauth_signature=oauth_signature)
                    add_message_access_token_status = add_message_access_token_res['status']
                    if add_message_access_token_status == 'OK':
                        add_message_access_token = add_message_access_token_res['access_token']

                        add_message_data = json.dumps(
                                {'message': message, 'chat_id': str(chat_id)}).encode('utf8')
                        add_message_url = api_url_path + add_message_request_query
                        add_message_query_data = get_data_from_url(request_query=add_message_request_query,
                                                                           access_token=add_message_access_token,
                                                                           auth_signature=oauth_signature,
                                                                           data=add_message_data,
                                                                           url=add_message_url, session_id=session_id)

                        if add_message_query_data['status'] == 'OK':
                            response_data = {}
                            response_data['type'] = 'message'
                            response_data['chat_id'] = chat_id
                            response_data['message'] = message
                            response_data['client_status'] = client_status
                            response_data['client_display_name'] = client_display_name
                            response_data['client_image_url'] = client_image_url

                            ws_user.sendMessage(u'' + json.dumps(response_data))
                            ws_admin.sendMessage(u'' + json.dumps(response_data))

    def handleConnected(self):
        clients_pull.append(self)

    def handleClose(self):
        disconnected_client_id = int
        disconnected_client_status = str
        disconnected_from_chat_id = str

        # Search disconnected_client in waiting chats
        for waiting_chat in waiting_chats:
            ws_user = waiting_chat.ws_user.client

            if ws_user == self:
                disconnected_from_chat_id = waiting_chat.chat_id
                disconnected_user_id = waiting_chat.ws_user.user_id
                disconnect_state_data = json.dumps(
                    {'client_connect_state': 'disconnect', 'client_status': 'user',
                     'client_id': str(disconnected_user_id), 'chat_id': str(disconnected_from_chat_id)}).encode('utf8')
                request_name = 'support_service/client_connect_state'
                disconnect_state_url = api_url_path + request_name

                session_id = waiting_chat.ws_user.user_session_id
                # Create user signature
                hash_object = hashlib.sha1(site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(disconnected_user_id).encode('utf-8'))
                oauth_signature = hash_object.hexdigest()

                access_token_res = get_access_token(request_query=request_name, oauth_signature=oauth_signature)
                access_token_status = access_token_res['status']

                if access_token_status == 'OK':
                    access_token = access_token_res['access_token']
                    write_to_log(u'disconnect_access_token:  ' + str(access_token))
                    disconnect_user_query_data = get_data_from_url(request_query=request_name, access_token=access_token, auth_signature=oauth_signature, data=disconnect_state_data, url=disconnect_state_url, session_id=session_id)
                    write_to_log(u'disconnect_user_query_data:  ' + str(disconnect_user_query_data['status']))
                    if disconnect_user_query_data['status'] == 'OK':

                        waiting_chats.remove(waiting_chat)
                        write_to_log(u'user disconnect: OK waiting_chat client_id - ' + str(disconnected_user_id) + u', chat_id - ' + str(disconnected_from_chat_id))

                        response_data = {}
                        update_waiting_users_list_for_admins(response_data)

        # Search disconnected_client in accepted chats
        for accepted_chat in accepted_chats:
            ws_user = accepted_chat.ws_user.client
            if ws_user == self:
                disconnected_from_chat_id = accepted_chat.chat_id
                disconnected_user_id = accepted_chat.ws_user.user_id
                disconnect_state_data = json.dumps(
                    {'client_connect_state': 'disconnect', 'client_status': 'user',
                     'client_id': str(disconnected_user_id),
                     'chat_id': str(disconnected_from_chat_id)}).encode('utf8')
                disconnect_state_url = api_url_path + request_name

                session_id = accepted_chat.ws_user.user_session_id
                # Create user signature
                hash_object = hashlib.sha1(site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(disconnected_user_id).encode('utf-8'))
                oauth_signature = hash_object.hexdigest()

                access_token_res = get_access_token(request_query=request_name, oauth_signature=oauth_signature)
                access_token_status = access_token_res['status']

                if access_token_status == 'OK':
                    access_token = access_token_res['access_token']
                    disconnect_user_query_data = get_data_from_url(request_query=request_name,
                                                                       access_token=access_token,
                                                                       auth_signature=oauth_signature,
                                                                       data=disconnect_state_data,
                                                                       url=disconnect_state_url, session_id=session_id)
                    write_to_log(u'disconnect_accepted_chat_user_query_data:  ' + str(disconnect_user_query_data['status']))
                    if disconnect_user_query_data['status'] == 'OK':

                        write_to_log(u'user disconnect: OK accepted_chat client_id - ' + str(disconnected_user_id) + u', chat_id - ' + str(disconnected_from_chat_id))

                        response_data = {}
                        response_data['type'] = 'user_disconnected'
                        response_data['chat_id'] = disconnected_from_chat_id
                        accepted_chat.ws_admin.client.sendMessage(u'' + json.dumps(response_data))

            if accepted_chat.ws_admin.client == self:
                disconnected_from_chat_id = accepted_chat.chat_id
                disconnected_admin_id = accepted_chat.ws_admin.admin_id
                disconnected_admin_display_name = accepted_chat.ws_admin.admin_display_name
                disconnected_admin_image_url = accepted_chat.ws_admin.admin_image_url
                disconnected_admin_oauth_signature = accepted_chat.ws_admin.admin_oauth_signature
                disconnected_admin_page_session_id = accepted_chat.ws_admin.admin_page_session_id

                disconnect_state_data = json.dumps(
                                {'client_connect_state': 'disconnect', 'client_status': 'admin',
                                 'client_id': str(disconnected_admin_id),
                                 'chat_id': str(disconnected_from_chat_id), 'page_session_id': disconnected_admin_page_session_id}).encode('utf8')
                disconnect_state_url = api_url_path + request_name

                access_token_res = get_access_token(request_query=request_name, oauth_signature=disconnected_admin_oauth_signature)
                access_token_status = access_token_res['status']

                if access_token_status == 'OK':
                    access_token = access_token_res['access_token']
                    write_to_log(u'disconnect_accepted_chat_admin_access_token1:  ' + str(access_token))
                    disconnect_user_query_data = get_data_from_url(request_query=request_name,
                                                                       access_token=access_token,
                                                                       auth_signature=disconnected_admin_oauth_signature,
                                                                       data=disconnect_state_data,
                                                                       url=disconnect_state_url, session_id='')
                    write_to_log(u'disconnect_accepted_chat_admin_query_data:  ' + str(disconnect_user_query_data['status']))
                    if disconnect_user_query_data['status'] == 'OK':

                        write_to_log(u'admin disconnect: OK client_id - ' + str(disconnected_admin_id) + u', chat_id - ' + str(disconnected_from_chat_id))

                        response_data = {}
                        response_data['type'] = 'admin_disconnected'
                        response_data['admin_display_name'] = disconnected_admin_display_name
                        response_data['admin_image_url'] = disconnected_admin_image_url
                        accepted_chat.ws_user.client.sendMessage(u'' + json.dumps(response_data))

        # Search disconnected_client in support admins list
        for ws_admin in support_admins:
            if ws_admin.client == self:
                disconnected_admin_id = accepted_chat.ws_admin.admin_id
                disconnected_admin_display_name = accepted_chat.ws_admin.admin_display_name
                disconnected_admin_image_url = accepted_chat.ws_admin.admin_image_url
                disconnected_admin_oauth_signature = accepted_chat.ws_admin.admin_oauth_signature
                disconnected_admin_page_session_id = accepted_chat.ws_admin.admin_page_session_id

                admin_connect_state_request_name = 'support_service/support_admin_connect_state'
                disconnect_state_url = api_url_path + admin_connect_state_request_name
                access_token_res = get_access_token(request_query=admin_connect_state_request_name, oauth_signature=disconnected_admin_oauth_signature)
                access_token_status = access_token_res['status']

                if access_token_status == 'OK':
                    access_token = access_token_res['access_token']
                    disconnect_state_data = json.dumps({'client_connect_state': 'disconnect', 'page_session_id': disconnected_admin_page_session_id}).encode('utf8')
                    disconnect_user_query_data = get_data_from_url(request_query=admin_connect_state_request_name,
                                                                       access_token=access_token,
                                                                       auth_signature=disconnected_admin_oauth_signature,
                                                                       data=disconnect_state_data,
                                                                       url=disconnect_state_url, session_id='')
                    write_to_log(
                        u'disconnect_accepted_chat_admin_query_data:  ' + str(disconnect_user_query_data['status']))
                    if disconnect_user_query_data['status'] == 'OK':
                        write_to_log(
                            u'admin disconnect: OK client_id - ' + str(disconnected_admin_id) + u', chat_id - ' + str(
                                disconnected_from_chat_id))


                support_admins.remove(ws_admin)
                response_data = {}
                response_data['type'] = 'online_admins'
                response_data['online_admins'] = len(support_admins)
                for waiting_chat in waiting_chats:
                    waiting_chat.ws_user.client.sendMessage(u'' + json.dumps(response_data))


if __name__ == "__main__":

    parser = OptionParser(usage="usage: %prog [options]", version="%prog 1.0")
    parser.add_option("--host", default='', type='string', action="store", dest="host", help="hostname (localhost)")
    parser.add_option("--port", default=8888, type='int', action="store", dest="port", help="port (8888)")

    if VER >= 3:
        parser.add_option("--ssl", default=1, type='int', action="store", dest="ssl", help="ssl (1: on, 0: off (default))")
        parser.add_option("--cert", default='/www/ssl_cert/cert.pem', type='string',
                          action="store", dest="cert", help="cert (/www/ssl_cert/cert.pem)")
        parser.add_option("--ver", default=ssl.PROTOCOL_TLSv1, type=int, action="store", dest="ver", help="ssl version")
    else:
        parser.add_option("--ssl", default=0, type='int', action="store", dest="ssl", help="ssl (1: on, 0: off (default))")


    (options, args) = parser.parse_args()

    cls = SupportChat

    if options.ssl == 1:
        server = SSLWebSocketServer(options.host, options.port, cls, options.cert, options.cert, version=options.ver)
    else:
        server = WebSocketServer(options.host, options.port, cls)

    def close_sig_handler(signal, frame):
        server.close()
        sys.exit()

    def get_access_token(request_query, oauth_signature):
        get_access_token_url = api_url_path + 'auth/access_token'
        req = Request(get_access_token_url)
        req.add_header('Request-Type', request_query)
        req.add_header('Auth-Signature', oauth_signature)
        json_data = json.loads(urlopen(req).read().decode())
        return json_data

    def get_data_from_url(request_query, access_token, data, auth_signature, url, session_id):
        if data:
            req = Request(url, data=data)
            req.add_header('Content-Type', 'application/json')
        else:
            req = Request(url)

        if session_id != '':
            req.add_header('Session-Id', session_id)

        req.add_header('Request-Type', request_query)
        req.add_header('Auth-Signature', auth_signature)
        req.add_header('Access-Token', access_token)

        json_data = json.loads(urlopen(req).read().decode())
        return json_data

    def update_waiting_users_list_for_admins(response_data):
        waiting_users = {}
        for waiting_chat_data in waiting_chats:
            waiting_chat_id = waiting_chat_data.chat_id
            waiting_user_id = waiting_chat_data.ws_user.user_id
            waiting_user_display_name = waiting_chat_data.ws_user.user_display_name
            waiting_user_image_url = waiting_chat_data.ws_user.user_image_url
            created_timestamp = waiting_chat_data.created_timestamp
            waiting_user_messages = waiting_chat_data.messages
            waiting_users[waiting_chat_id] = [waiting_user_id, waiting_user_display_name, waiting_user_image_url, created_timestamp, waiting_user_messages]

        for ws_admin in support_admins:
            response_data['type'] = 'waiting_chats'
            response_data['waiting_chats'] = waiting_users
            ws_admin.client.sendMessage(u'' + json.dumps(response_data))

    def send_email_to_admins(email_type, chat_id, oauth_signature, session_id):
        support_admins_id = []

        request_query_admins_status = 'support_service/admins_status'
        access_token_res = get_access_token(request_query=request_query_admins_status, oauth_signature=oauth_signature)
        access_token_status = access_token_res['status']

        if access_token_status == 'OK':
            access_token = access_token_res['access_token']
            admins_data_url = api_url_path + 'support_service/admins_status'
            admins_data = get_data_from_url(request_query=request_query_admins_status, access_token=access_token, auth_signature=oauth_signature, data='', url=admins_data_url, session_id=session_id)
            for admin_id in admins_data['admins_list']:
                support_admins_id.append(admin_id)

            for admin in support_admins_id:
                request_query = 'support_service/admin'
                access_token_res = get_access_token(request_query=request_query, oauth_signature=oauth_signature)
                access_token_status = access_token_res['status']
                if access_token_status == 'OK':
                    access_token = access_token_res['access_token']
                    client_data_url = api_url_path + 'support_service/admin/' + str(admin)
                    admin_data = get_data_from_url(request_query=request_query, access_token=access_token,
                                                       auth_signature=oauth_signature, data='', url=client_data_url,
                                                       session_id=session_id)
                    if admin_data['status'] == 'OK':
                        email_to = admin_data['data']['email']

                        if email_to and not re.findall('gmail.com', email_to):
                            for waiting_chat_data in waiting_chats:
                                if int(waiting_chat_data.chat_id) == int(chat_id):
                                    waiting_chat_id = waiting_chat_data.chat_id
                                    waiting_user_display_name = waiting_chat_data.ws_user.user_display_name
                                    waiting_user_image_url = waiting_chat_data.ws_user.user_image_url

                                    email_from = "davletshina.adel@gmail.com"

                                    msg = MIMEMultipart('alternative')
                                    msg['From'] = email_from
                                    msg['To'] = email_to

                                    # Send message about new client in support chat to support admins
                                    if email_type == 'new_chat':
                                        msg['Subject'] = "Support service. New chat opened"
                                        text = "New chat #" + str(waiting_chat_id) + " " + waiting_user_display_name
                                        html = """\
                                            <html>
                                              <head></head>
                                              <body>
                                                <p>New chat #""" + str(waiting_chat_id) + """</p><br>
                                                <div style="display: flex; align-items: center;"><div class="profile_image" style="background: url(""" + waiting_user_image_url + """) 100% 100% no-repeat;  background-size: cover; display: inline-block; margin-right: 5px; width: 30px; height: 30px; border-radius: 50%;"></div><div>""" + waiting_user_display_name + """</div>
                                              </body>
                                            </html>
                                            """

                                    # Send message about new message in waiting chat to support admins
                                    if email_type == 'new_message':
                                        messages = waiting_chat_data.messages
                                        if messages:
                                            messages_text = ''
                                            messages_html = ''

                                            for timestamp, message in messages.items():
                                                message_created_date = datetime.datetime.fromtimestamp(timestamp)
                                                message_date = message_created_date.strftime('%d.%m.%Y %H:%M:%S')
                                                messages_text += message_date + ': ' + message + '<br>'
                                                messages_html += '<div><span style="color:#8e9090; margin-right:10px;">' + message_date + ':</span>' + message + '</div><br>'

                                            msg['Subject'] = "Support service. New waiting message"
                                            text = "New chat #" + str(
                                                waiting_chat_id) + " " + waiting_user_display_name + "<br>" + messages_text
                                            html = """\
                                                    <html>
                                                      <head></head>
                                                      <body>
                                                        <p>New chat #""" + str(waiting_chat_id) + """</p><br>
                                                        <div style="display: flex; align-items: center;"><div class="profile_image" style="background: url(""" + waiting_user_image_url + """) 100% 100% no-repeat;  background-size: cover; display: inline-block; margin-right: 5px; width: 30px; height: 30px; border-radius: 50%;"></div><div>""" + waiting_user_display_name + """</div></div><br>
                                                       """ + messages_html + """
                                                      </body>
                                                    </html>
                                                    """

                                    if text and html:
                                        part1 = MIMEText(text, 'plain')
                                        part2 = MIMEText(html, 'html')

                                        msg.attach(part1)
                                        msg.attach(part2)

                                        s = smtplib.SMTP('localhost')
                                        s.sendmail(email_from, email_to, msg.as_string())
                                        s.quit()
                        else:
                            write_to_log(u'Error send email to' + email_to)
        else:
            write_to_log(u'access_token_data: access_token_status - ' + str(access_token_status) + u', message - ' + str(access_token_res['message']))




    def write_to_log(data):
        datestr = time.strftime('%d%m%Y')
        f = open('/www/websocket_server_support/logs/log_' + datestr + '.log', 'a')
        timestr = time.strftime("[%H:%M]")
        f.write(timestr + ' ' + data + '\n')
        f.close()

    signal.signal(signal.SIGINT, close_sig_handler)

    server.serveforever()