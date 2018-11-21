import time, signal, sys, ssl, json, hashlib, smtplib, re, datetime

from websocket_server import WebSocket, WebSocketServer, SSLWebSocketServer
from optparse import OptionParser

# Check python version
VER = sys.version_info[0]
# print(u'python_VER: ' + str(VER))
try:
    from urllib.request import Request, urlopen  # Python 3
except:
    from urllib2 import Request, urlopen  # Python 2

clients_pull = []
chats_array = []

# ===== Don't change =====
site_auth_login = ''
site_auth_pass = ''
# ===== Don't change =====

api_url_path = '/api/v1/'


class UsersChatWrapper():
    def __init__(self, chat_id):
        self.chat_id = chat_id
        self.ws_members_array = []

    def add_to_members_array(self, member):
        self.ws_members_array.append(member)


class WsUserWrapper():
    def __init__(self, client, user_id, display_name, image_url, oauth_signature, session_id):
        self.client = client
        self.user_id = user_id
        self.display_name = display_name
        self.image_url = image_url
        self.oauth_signature = oauth_signature
        self.session_id = session_id
        self.tokens = {}

    def update_tokens(self, token_data):
        self.tokens.update(token_data)


class UsersChat(WebSocket):

    def handleMessage(self):
        json_data = json.loads(self.data)
        type = json_data['type']
        # print(json_data)

        if type == 'message':
            original_message = json_data['message']
            message = original_message.replace("'", r"\'")
            chat_id = int
            send_message = False
            # try:
            #     print (message)
            # except Exception as e:
            #     print(str(e))

            for chat in chats_array:
                members_array = chat.ws_members_array

                # Create connected users id array to current chat
                connected_users_id_array = []
                for ws_member in members_array:
                    connected_user_id = ws_member.user_id
                    connected_users_id_array.append(connected_user_id)

                for ws_member in members_array:
                    ws_user = ws_member.client

                    if ws_user == self and original_message:

                        chat_id = chat.chat_id

                        user_id = ws_member.user_id
                        display_name = ws_member.display_name
                        image_url = ws_member.image_url
                        oauth_signature = ws_member.oauth_signature
                        session_id = ws_member.session_id

                        message_add_request_name = 'users_chat/message_add'

                        # Use existing access token or get new one
                        access_token_res = use_access_token(ws_user=ws_member, token_type=type)
                        if access_token_res['status'] == 'OK':
                            connected_users_id_array.remove(user_id)
                            access_token = access_token_res['access_token']
                            message_add_data = json.dumps(
                                {'chat_id': chat_id, 'message': message,
                                 'connected_users_id_array': connected_users_id_array}).encode('utf-8')
                            message_add_url = api_url_path + message_add_request_name

                            message_add_response = get_data_from_url(request_name=message_add_request_name,
                                                                     access_token=access_token,
                                                                     auth_signature=oauth_signature,
                                                                     data=message_add_data,
                                                                     url=message_add_url,
                                                                     session_id=session_id)
                            if message_add_response['status'] == 'OK':
                                write_to_log(
                                    u'user message: OK user_id - ' + str(user_id) + u', chat_id - ' + str(chat_id))
                                message_id = message_add_response['message_id']
                                timestamp = message_add_response['timestamp']
                                message_created_time = datetime.datetime.fromtimestamp(int(timestamp)).strftime('%H:%M')

                                send_message = True

                                response_data = {}
                                response_data['type'] = type
                                response_data['chat_id'] = chat_id
                                response_data['message_id'] = message_id
                                response_data['message'] = original_message
                                response_data['timestamp'] = timestamp
                                response_data['message_created_time'] = message_created_time
                                response_data['user_id'] = user_id
                                response_data['display_name'] = display_name
                                response_data['image_url'] = image_url

            for ch in chats_array:
                wanted_chat_id = ch.chat_id

                if wanted_chat_id == chat_id and send_message:
                    ws_members_array = ch.ws_members_array
                    for member in ws_members_array:
                        member.client.sendMessage(u'' + json.dumps(response_data))

        if type == 'system_message_connect':

            user_id = int(json_data['user_id'])
            chat_id = int(json_data['chat_id'])

            create_chat_wrapper = True
            create_user_wrapper = True
            create_ws_user = False

            for chat in chats_array:
                current_chat_id = chat.chat_id
                members_array = chat.ws_members_array

                for ws_member in members_array:
                    ws_user = ws_member.client

                    if ws_user == self:
                        write_to_log(u'user connect exist: chat_id - ' + str(chat_id) + u'user_id ' + str(ws_member.user_id))
                        create_user_wrapper = False
                        create_chat_wrapper = False
                        break

                if current_chat_id == chat_id:
                    create_chat_wrapper = False

                    for ws_member in members_array:
                        if ws_member.user_id == user_id:
                            create_user_wrapper = False

            if create_user_wrapper:
                if 'device_uuid' in json_data:
                    device_uuid = json_data['device_uuid']
                    session_id = json_data['session_id']
                    # Create user/device signature
                    hash_object = hashlib.sha1(user_id.encode('utf-8') + device_uuid.encode('utf-8'))
                    oauth_signature = hash_object.hexdigest()
                else:
                    session_id = json_data['sid']
                    # Create web-user signature
                    hash_object = hashlib.sha1(
                        site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(user_id).encode('utf-8'))
                    oauth_signature = hash_object.hexdigest()

                request_name = 'users_chat/connect'

                # Get access token
                access_token_for_connect_res = get_access_token(request_name=request_name, oauth_signature=oauth_signature)

                if access_token_for_connect_res['status'] == 'OK':

                    access_token_connect_data = access_token_for_connect_res['access_token']

                    write_to_log(
                        u'system_message_connect access_token_for_connect_res: OK user_id - ' + str(user_id) + u' , chat_id ' + str(chat_id))

                    connect_state_url = api_url_path + request_name + '/' + str(chat_id)

                    connect_state_data = get_data_from_url(request_name=request_name, access_token=access_token_connect_data,
                                                    auth_signature=oauth_signature, data='', url=connect_state_url,
                                                    session_id=session_id)

                    if connect_state_data['status'] == 'OK':

                        display_name = connect_state_data['user_data']['display_name']
                        image_url = connect_state_data['user_data']['profile_image']

                        create_ws_user = True

                        ws_user = WsUserWrapper(self, user_id=user_id, display_name=display_name, image_url=image_url, oauth_signature=oauth_signature, session_id=session_id)
                        write_to_log(
                            u'system_message_connect user ws created: OK user_id - ' + str(user_id) + u' , chat_id ' + str(chat_id))

                    else:
                        create_ws_user = False
                        write_to_log(
                            u'system_message_connect user ws created: ACCESS-DENIED user_id - ' + str(user_id) + u' , chat_id ' + str(chat_id))

                    response_data = {}
                    response_data['type'] = type
                    response_data['state'] = connect_state_data['status']
                    response_data['chat_id'] = chat_id
                    self.sendMessage(u'' + json.dumps(response_data))

                write_to_log(u'system_message_connect user ws visible here: OK user_id - ' + str(user_id) + u' , chat_id ' + str(chat_id))

                if create_ws_user:

                    if create_chat_wrapper:

                        chat_wrapper = UsersChatWrapper(chat_id=chat_id)
                        chat_wrapper.add_to_members_array(member=ws_user)
                        chats_array.append(chat_wrapper)
                        write_to_log(u'system_message_connect user connect to new chat: OK user_id - ' + str(user_id) + u' , chat_id ' + str(chat_id))

                    else:

                        for chat in chats_array:
                            if chat.chat_id == chat_id and create_ws_user:

                                write_to_log(u'system_message_connect user connect to existing chat: user_id - ' + str(user_id) +  u', chat_id - ' + str(chat_id) + u', users count before - ' + str(len(chat.ws_members_array)))

                                chat.add_to_members_array(member=ws_user)

                                write_to_log(u'system_message_connect user connect to existing chat: chat_id - ' + str(
                                    chat_id) + u', users count after - ' + str(len(chat.ws_members_array)))

                clients_pull.remove(self)

        if type == 'system_message_create':

            user_id = int(json_data['user_id'])
            invited_user_id = int(json_data['invited_user_id'])

            create_user_wrapper = True

            for chat in chats_array:
                current_chat_id = chat.chat_id
                members_array = chat.ws_members_array

                for ws_member in members_array:
                    ws_user = ws_member.client

                    if ws_user == self:
                        write_to_log(
                            u'user client exist: chat_id - ' + str(current_chat_id) + u'user_id' + str(ws_member.user_id))
                        create_user_wrapper = False
                        break

            if create_user_wrapper:
                if 'device_uuid' in json_data:
                    device_uuid = json_data['device_uuid']
                    session_id = json_data['session_id']
                    # Create user/device signature
                    hash_object = hashlib.sha1(user_id.encode('utf-8') + device_uuid.encode('utf-8'))
                    oauth_signature = hash_object.hexdigest()
                else:
                    session_id = json_data['sid']
                    # Create web-user signature
                    hash_object = hashlib.sha1(
                        site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(user_id).encode('utf-8'))
                    oauth_signature = hash_object.hexdigest()

                request_name = 'users_chat/create'

                # Get access token
                access_token_for_create_res = get_access_token(request_name=request_name, oauth_signature=oauth_signature)
                if access_token_for_create_res['status'] == 'OK':

                    access_token_for_connect_data = access_token_for_create_res['access_token']
                    write_to_log(
                        u'user ws created: OK user_id - ' + str(user_id) + u' , access_token ' + str(
                            access_token_for_connect_data))

                    create_state_url = api_url_path + request_name + '/' + str(invited_user_id)

                    create_state_data = get_data_from_url(request_name=request_name,
                                                           access_token=access_token_for_connect_data,
                                                           auth_signature=oauth_signature, data='',
                                                           url=create_state_url,
                                                           session_id=session_id)

                    response_data = {}
                    response_data['type'] = type
                    response_data['state'] = create_state_data['status']

                    if create_state_data['status'] == 'OK':

                        chat_id = create_state_data['chat_id']
                        display_name = create_state_data['user_data']['display_name']
                        image_url = create_state_data['user_data']['profile_image']

                        ws_user = WsUserWrapper(self, user_id=user_id, display_name=display_name, image_url=image_url,
                                                oauth_signature=oauth_signature, session_id=session_id)

                        chat_wrapper = UsersChatWrapper(chat_id=chat_id)
                        chat_wrapper.add_to_members_array(member=ws_user)
                        chats_array.append(chat_wrapper)
                        write_to_log(
                            u'user create new chat: OK user_id - ' + str(user_id) + u' , chat_id ' + str(chat_id))

                        response_data['chat_id'] = chat_id
                    else:
                        write_to_log(u'user ws created: ACCESS-DENIED user_id - ' + str(user_id))

                    self.sendMessage(u'' + json.dumps(response_data))

                write_to_log(u'user ws visible here: OK user_id - ' + str(user_id))

                clients_pull.remove(self)

        # invite more members to existing chat
        if type == 'invite':
            invited_user_id = json_data['invited_user_id']
            chat_id = int
            response_data = {}

            for chat in chats_array:
                members_array = chat.ws_members_array

                for ws_member in members_array:
                    ws_user = ws_member.client

                    if ws_user == self:
                        chat_id = chat.chat_id

                        user_id = ws_member.user_id
                        display_name = ws_member.display_name
                        image_url = ws_member.image_url
                        oauth_signature = ws_member.oauth_signature
                        session_id = ws_member.session_id

                        invite_request_name = 'users_chat/invite'

                        # Use existing access token or get new one
                        access_token_res = use_access_token(ws_user=ws_member, token_type=type)

                        if access_token_res['status'] == 'OK':
                            access_token = access_token_res['access_token']
                            data = json.dumps({'chat_id': chat_id, 'invited_user_id': invited_user_id}).encode('utf-8')
                            invite_url = api_url_path + invite_request_name
                            invite_response = get_data_from_url(request_name=invite_request_name,
                                                                     access_token=access_token,
                                                                     auth_signature=oauth_signature,
                                                                     data=data,
                                                                     url=invite_url,
                                                                     session_id=session_id)
                            if invite_response['status'] == 'OK':
                                write_to_log(
                                    u'user invite: OK user_id - ' + str(user_id) + u', chat_id - ' + str(chat_id))
                                message_id = invite_response['message_id']
                                timestamp = invite_response['timestamp']
                                invited_user_display_name = invite_response['invited_user_display_name']

                                response_data['type'] = type
                                response_data['chat_id'] = chat_id
                                response_data['message_id'] = message_id
                                response_data['invited_user_id'] = invited_user_id
                                response_data['invited_user_display_name'] = invited_user_display_name
                                response_data['timestamp'] = timestamp
                                response_data['user_id'] = user_id
                                response_data['display_name'] = display_name
                                response_data['image_url'] = image_url

            for ch in chats_array:
                wanted_chat_id = ch.chat_id
                if wanted_chat_id == chat_id and response_data:
                    ws_members_array = ch.ws_members_array
                    for member in ws_members_array:
                        member.client.sendMessage(u'' + json.dumps(response_data))

                    write_to_log(u'user invite: OK send to - ' + str(
                        len(ch.ws_members_array)))

    def handleConnected(self):
        clients_pull.append(self)

    def handleClose(self):
        disconnected_user_id = int
        disconnected_from_chat_id = str

        # Search disconnected client in chats
        for chat in chats_array:
            members_array = chat.ws_members_array
            disconnected_from_chat_id = chat.chat_id

            write_to_log(
                u'user before disconnect info:  members_count - ' + str(len(members_array)) + u', chat_id - ' + str(disconnected_from_chat_id))

            for ws_member in members_array:
                ws_user = ws_member.client
                # Check client
                if ws_user == self:

                    disconnected_user_id = ws_member.user_id
                    write_to_log(u'user disconnect: Ok  ' + str(disconnected_user_id) + u', chat_id - ' + str(disconnected_from_chat_id))

                    # Remove disconnected client from chat
                    members_array.remove(ws_member)

                    write_to_log(
                        u'user after disconnect info:  members_count - ' + str(len(members_array)) + u', chat_id - ' + str(disconnected_from_chat_id))

                if len(members_array) == 0:
                    # Remove chat if all clients disconnected
                    chats_array.remove(chat)

                    write_to_log(u'user after disconnect info:  chat deleted chat_id - ' + str(disconnected_from_chat_id))

if __name__ == "__main__":

    parser = OptionParser(usage="usage: %prog [options]", version="%prog 1.0")
    parser.add_option("--host", default='', type='string', action="store", dest="host", help="hostname (localhost)")
    parser.add_option("--port", default=8009, type='int', action="store", dest="port", help="port (8009)")

    if VER >= 3:
        parser.add_option("--ssl", default=1, type='int', action="store", dest="ssl", help="ssl (1: on, 0: off (default))")
        parser.add_option("--cert", default='/www/ssl_cert/cert.pem', type='string',
                          action="store", dest="cert", help="cert (/www/ssl_cert/cert.pem)")
        parser.add_option("--ver", default=ssl.PROTOCOL_TLSv1, type=int, action="store", dest="ver", help="ssl version")
    else:
        parser.add_option("--ssl", default=0, type='int', action="store", dest="ssl", help="ssl (1: on, 0: off (default))")


    (options, args) = parser.parse_args()

    cls = UsersChat

    if options.ssl == 1:
        server = SSLWebSocketServer(options.host, options.port, cls, options.cert, options.cert, version=options.ver)
    else:
        server = WebSocketServer(options.host, options.port, cls)

    def close_sig_handler(signal, frame):
        server.close()
        sys.exit()

    def get_access_token(request_name, oauth_signature):
        get_access_token_url = api_url_path + 'auth/access_token'
        req = Request(get_access_token_url)
        req.add_header('Request-Type', request_name)
        req.add_header('Auth-Signature', oauth_signature)
        json_data = json.loads(urlopen(req).read().decode())
        return json_data


    def use_access_token(ws_user, token_type):
        user_tokens = ws_user.tokens
        oauth_signature = ws_user.oauth_signature
        filter_date = time.time()

        # Check existing access_token
        if token_type in user_tokens:
            if int(user_tokens[token_type]['expired_date']) > filter_date:
                access_token = user_tokens[token_type]['access_token']

                response = {}
                response['status'] = 'OK'
                response['access_token'] = access_token

                return response

        # Get request_name to get new access_token
        valid_types_array = {'message': 'users_chat/message_add', 'invite': 'users_chat/invite'}
        if token_type in valid_types_array:
            request_name = valid_types_array[token_type]

        access_token_res = get_access_token(request_name, oauth_signature)
        if access_token_res['status'] == 'OK':
            access_token = access_token_res['access_token']
            expired_date = access_token_res['expired_date']

            # Update user ws (put new token)
            token_data = {}
            token_data['access_token'] = access_token
            token_data['expired_date'] = expired_date
            token_data_dict = {token_type: token_data}
            ws_user.update_tokens(token_data_dict)

            response = {}
            response['status'] = 'OK'
            response['access_token'] = access_token
            return response

    def get_data_from_url(request_name, access_token, data, auth_signature, url, session_id):
        if data:
            req = Request(url, data=data)
            req.add_header('Content-Type', 'application/json')
        else:
            req = Request(url)

        req.add_header('Request-Type', request_name)
        req.add_header('Auth-Signature', auth_signature)
        req.add_header('Access-Token', access_token)
        req.add_header('Session-Id', session_id)

        json_data = json.loads(urlopen(req).read().decode())
        return json_data

    def write_to_log(data):
        datestr = time.strftime('%d%m%Y')
        f = open('/www/websocket_server_users_chat/logs/log_' + datestr + '.log', 'a')
        timestr = time.strftime("[%H:%M]")
        f.write(timestr + ' ' + data + '\n')
        f.close()

    signal.signal(signal.SIGINT, close_sig_handler)

    server.serveforever()