import time, signal, sys, ssl, json, hashlib
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
streams_clients = {}
stream_clients_ids = []

# ===== Don't change =====
site_auth_login = ''
site_auth_pass = ''
# ===== Don't change =====

class WebSocketWrapper():
    def __init__(self, client, client_id, device_uuid, session_id):
        self.client = client
        self.client_id = client_id
        self.device_uuid = device_uuid
        self.session_id = session_id


class Chat(WebSocket):

    def handleMessage(self):
        json_data = json.loads(self.data)
        type = json_data['type']
        stream_uuid = json_data['stream_uuid']
        client_id = json_data['client_id']

        if ('lang' in json_data) and (json_data['lang'] in ['ru', 'en']):
            lang = json_data['lang']
        else:
            lang = 'ru'

        if 'device_uuid' in json_data:
            device_uuid = json_data['device_uuid']
            session_id = json_data['session_id']
            # Create user/device signature
            hash_object = hashlib.sha1(client_id.encode('utf-8') + device_uuid.encode('utf-8'))
            oauth_signature = hash_object.hexdigest()
        else:
            session_id = ''
            # Create web_user signature
            hash_object = hashlib.sha1(site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(client_id).encode('utf-8'))
            oauth_signature = hash_object.hexdigest()

        # Get access tokens
        access_token_stream_data_res = get_access_token(request_query='streams/stream', oauth_signature=oauth_signature)
        access_token_client_data_res = get_access_token(request_query='users/user', oauth_signature=oauth_signature)

        access_token_stream_data_status = access_token_stream_data_res['status']
        access_token_client_data_status = access_token_client_data_res['status']
        if (access_token_stream_data_status == 'OK') & (access_token_client_data_status == 'OK'):
            access_token_stream_data = access_token_stream_data_res['access_token']
            access_token_client_data = access_token_client_data_res['access_token']

            stream_data_url = '/api/v1/streams/stream/' + stream_uuid
            client_data_url = '/api/v1/users/user/' + str(client_id)
            data = json.dumps({'user_id': client_id}).encode('utf8')

            stream_data = get_data_from_url(request_query='streams/stream', access_token=access_token_stream_data, auth_signature=oauth_signature, data='', url=stream_data_url, session_id=session_id)

            client_data = get_data_from_url(request_query='users/user', access_token=access_token_client_data, auth_signature=oauth_signature, data=data, url=client_data_url, session_id=session_id)

            # if 'timestamp' in json_data:
            #     timestamp = json_data['timestamp']
            # else:
            #     timestamp = time.time()

            timestamp = time.time()

            client_data_status = client_data['status']
            stream_data_status = stream_data['status']
            if (client_data_status == 'OK') & (stream_data_status == 'OK'):
                client_display_name = client_data['data']['display_name']
                client_image_url = client_data['data']['profile_image']
                blocked_users_array = client_data['data']['blocked']
                stream_likes_count = stream_data['data']['likes_count']
                stream_start_date_float = stream_data['data']['start_date_float']

                response_data = {}
                response_data['type'] = type
                response_data['client_id'] = client_id
                response_data['client_display_name'] = client_display_name
                response_data['client_image_url'] = client_image_url
                response_data['timestamp'] = timestamp

                if type == 'system_message':
                    if stream_uuid not in streams_clients:
                        streams_clients[stream_uuid] = []

                    if 'device_uuid' in json_data:
                        device_uuid = json_data['device_uuid']

                        # Create user/device signature
                        hash_object = hashlib.sha1(client_id.encode('utf-8') + device_uuid.encode('utf-8'))
                        oauth_signature = hash_object.hexdigest()
                    else:
                        device_uuid = ''

                        # Create web_user signature
                        hash_object = hashlib.sha1(
                            site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(client_id).encode(
                                'utf-8'))
                        oauth_signature = hash_object.hexdigest()

                    ws = WebSocketWrapper(self, client_id, device_uuid, session_id)
                    streams_clients[stream_uuid].append(ws)
                    stream_clients_ids.append(client_id)
                    clients_pull.remove(self)

                    # Get access token
                    access_token_res = get_access_token(request_query='streams/connect', oauth_signature=oauth_signature)
                    if access_token_res['status'] == 'OK':
                        access_token = access_token_res['access_token']

                        change_connect_state_data = json.dumps({'uuid': stream_uuid, 'state': 'connect'}).encode('utf8')
                        change_connect_state_response = get_data_from_url(request_query='streams/connect',
                                                                          access_token=access_token,
                                                                          auth_signature=oauth_signature,
                                                                          data=change_connect_state_data,
                                                                          url='/api/v1/streams/connect', session_id=session_id)
                        if change_connect_state_response['status'] == 'OK':
                            write_to_log(u'new connect: OK client_id - ' + str(client_id))
                        else:
                            change_connect_status = change_connect_state_response['status']
                            write_to_log(u'new connect: ERROR client_id - ' + str(client_id) + ' ' + change_connect_status)

                    response_data['stream_clients'] = stream_clients_ids

                if type == 'message':
                    text = json_data['text']

                    if 'device_uuid' in json_data:
                        device_uuid = json_data['device_uuid']
                        # Create user/device signature
                        hash_object = hashlib.sha1(client_id.encode('utf-8') + device_uuid.encode('utf-8'))
                        oauth_signature = hash_object.hexdigest()
                    else:
                        # Create web_user signature
                        hash_object = hashlib.sha1(
                            site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(client_id).encode(
                                'utf-8'))
                        oauth_signature = hash_object.hexdigest()

                    # Get access token
                    access_token_res = get_access_token(request_query='actions/comment', oauth_signature=oauth_signature)
                    if access_token_res['status'] == 'OK':
                        access_token = access_token_res['access_token']
                        comment_action_data = json.dumps({'uuid': stream_uuid, 'comment': text}).encode('utf8')
                        comment_action_response = get_data_from_url(request_query='actions/comment',
                                                                    access_token=access_token,
                                                                    auth_signature=oauth_signature,
                                                                    data=comment_action_data,
                                                                    url='/api/v1/actions/comment', session_id=session_id)
                        if comment_action_response['status'] == 'OK':
                            comment_add_status = comment_action_response['status']
                            comment_id = comment_action_response['comment_id']

                            response_data['comment_add_status'] = comment_add_status
                            response_data['comment_id'] = comment_id
                            response_data['text'] = text

                if type == 'like':
                    if 'device_uuid' in json_data:
                        device_uuid = json_data['device_uuid']
                        # Create user/device signature
                        hash_object = hashlib.sha1(client_id.encode('utf-8') + device_uuid.encode('utf-8'))
                        oauth_signature = hash_object.hexdigest()
                    else:
                        # Create web_user signature
                        hash_object = hashlib.sha1(site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(client_id).encode('utf-8'))
                        oauth_signature = hash_object.hexdigest()

                    # Get access token
                    access_token_res = get_access_token(request_query='actions/like', oauth_signature=oauth_signature)
                    if access_token_res['status'] == 'OK':
                        access_token = access_token_res['access_token']
                        like_action_data = json.dumps({'stream_uuid': stream_uuid}).encode('utf8')
                        like_action_response = get_data_from_url(request_query='actions/like',
                                                                 access_token=access_token,
                                                                 auth_signature=oauth_signature,
                                                                 data=like_action_data,
                                                                 url='/api/v1/actions/like', session_id=session_id)
                        if like_action_response['status'] == 'OK':
                            user_like_state = like_action_response['state']
                            response_data['like_state'] = user_like_state
                            if user_like_state == 1:
                                stream_likes_count += 1
                            elif user_like_state == 0:
                                stream_likes_count -= 1

                    response_data['like_total'] = stream_likes_count

                if type == 'follow':
                    hero_id = json_data['hero_id']

                    if 'device_uuid' in json_data:
                        device_uuid = json_data['device_uuid']
                        # Create user/device signature
                        hash_object = hashlib.sha1(client_id.encode('utf-8') + device_uuid.encode('utf-8'))
                        oauth_signature = hash_object.hexdigest()
                    else:
                        # Create web_user signature
                        hash_object = hashlib.sha1(
                            site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(client_id).encode(
                                'utf-8'))
                        oauth_signature = hash_object.hexdigest()

                        # Get access tokens
                        access_token_res = get_access_token(request_query='actions/follow',
                                                                    oauth_signature=oauth_signature)

                        if access_token_res['status'] == 'OK':
                            access_token_client_data = access_token_res['access_token']
                            follow_action_url = '/api/v1/actions/follow'
                            follow_action_data = json.dumps(
                                {'hero_id': hero_id, 'lang': lang}).encode('utf8')
                            follow_action_response = get_data_from_url(request_query='actions/follow',
                                                                       access_token=access_token_client_data,
                                                                       auth_signature=oauth_signature, data=follow_action_data,
                                                                       url=follow_action_url, session_id=session_id)
                            if follow_action_response['status'] == 'OK':
                                response_data['follow_state'] = follow_action_response['state']
                                response_data['follow_icon_title'] = follow_action_response['title']

                if type == 'block':
                    # Get info about blocked user
                    blocked_user_id = json_data['blocked_user_id']

                    if 'device_uuid' in json_data:
                        device_uuid = json_data['device_uuid']
                        # Create user/device signature
                        hash_object = hashlib.sha1(client_id.encode('utf-8') + device_uuid.encode('utf-8'))
                        oauth_signature = hash_object.hexdigest()
                    else:
                        # Create web_user signature
                        hash_object = hashlib.sha1(
                            site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(client_id).encode(
                                'utf-8'))
                        oauth_signature = hash_object.hexdigest()

                    # Get access tokens
                    access_token_client_data_res = get_access_token(request_query='users/user',
                                                                    oauth_signature=oauth_signature)

                    if access_token_client_data_res['status'] == 'OK':
                        access_token_client_data = access_token_client_data_res['access_token']
                        client_data_url = '/api/v1/users/user/' + str(blocked_user_id)
                        blocked_user_data = get_data_from_url(request_query='users/user',
                                                              access_token=access_token_client_data,
                                                              auth_signature=oauth_signature, data='',
                                                              url=client_data_url, session_id=session_id)

                        if blocked_user_data['status'] == 'OK':
                            blocked_user_display_name = blocked_user_data['data']['display_name']
                            blocked_user_image_url = blocked_user_data['data']['profile_image']

                            access_token_block_action = get_access_token(request_query='actions/block',
                                                                            oauth_signature=oauth_signature)
                            if access_token_block_action['status'] == 'OK':
                                access_token_block_action = access_token_block_action['access_token']
                                block_action_url = '/api/v1/actions/block'
                                block_action_data = json.dumps({'blocked_user_id': blocked_user_id, 'stream_uuid': stream_uuid, 'lang': lang}).encode('utf8')
                                block_action_response = get_data_from_url(request_query='actions/block',
                                                                          access_token=access_token_block_action,
                                                                          auth_signature=oauth_signature,
                                                                          data=block_action_data,
                                                                          url=block_action_url, session_id=session_id)

                                if block_action_response['status'] == 'OK':
                                    response_data['client_id'] = blocked_user_id
                                    response_data['client_display_name'] = blocked_user_display_name
                                    response_data['client_image_url'] = blocked_user_image_url
                                    response_data['blocked_user_id'] = blocked_user_id
                                    response_data['block_state'] = block_action_response['state']
                                    response_data['block_icon_title'] = block_action_response['title']

                # Get info about stream location
                if type == 'loc':
                    loc_data = json_data['loc_data']
                    device_uuid = json_data['device_uuid']

                    # Create user/device signature
                    hash_object = hashlib.sha1(client_id.encode('utf-8') + device_uuid.encode('utf-8'))
                    oauth_signature = hash_object.hexdigest()

                    # Get access token
                    access_token_res = get_access_token(request_query='streams/loc', oauth_signature=oauth_signature)
                    if access_token_res['status'] == 'OK':
                        access_token = access_token_res['access_token']
                        data = json.dumps({'uuid': stream_uuid, 'client_id': client_id, 'device_uuid': device_uuid,
                                           'loc_data': loc_data, 'timestamp': timestamp}).encode('utf8')
                        loc_update_response = get_data_from_url(request_query='streams/loc', access_token=access_token, data=data, auth_signature=oauth_signature, url='/api/v1/streams/loc', session_id=session_id)
                        if loc_update_response['status'] == 'OK':
                            response_data['lat'] = loc_data[0]
                            response_data['lng'] = loc_data[1]

                # Get info about stream orientation
                if type == 'ori':
                    ori = json_data['ori']
                    device_uuid = json_data['device_uuid']
                    valid_ori_params = ['landscape left', 'landscape right', 'portrait', 'upside down']

                    # Create user/device signature
                    hash_object = hashlib.sha1(client_id.encode('utf-8') + device_uuid.encode('utf-8'))
                    oauth_signature = hash_object.hexdigest()

                    # Get access token
                    access_token_res = get_access_token(request_query='streams/ori', oauth_signature=oauth_signature)
                    if access_token_res['status'] == 'OK':
                        access_token = access_token_res['access_token']

                        if ori in valid_ori_params:
                            data = json.dumps({'uuid': stream_uuid, 'client_id': client_id, 'device_uuid': device_uuid, 'ori': ori, 'timestamp': timestamp}).encode('utf8')
                            ori_update_response = get_data_from_url(request_query='streams/ori', access_token=access_token, data=data, auth_signature=oauth_signature, url='/api/v1/streams/ori', session_id=session_id)

                            if ori_update_response['status'] == 'OK':
                                response_data['ori'] = ori

                # Get info about stream heading
                if type == 'heading':
                    heading = float(json_data['heading'])
                    device_uuid = json_data['device_uuid']

                    # Create user/device signature
                    hash_object = hashlib.sha1(client_id.encode('utf-8') + device_uuid.encode('utf-8'))
                    oauth_signature = hash_object.hexdigest()

                    # Get access token
                    access_token_res = get_access_token(request_query='streams/heading', oauth_signature=oauth_signature)
                    if access_token_res['status'] == 'OK':
                        access_token = access_token_res['access_token']

                        if 0 <= heading <= 359.9:
                            data = json.dumps({'uuid': stream_uuid, 'client_id': client_id, 'device_uuid': device_uuid, 'heading': heading, 'timestamp': timestamp}).encode('utf8')
                            heading_update_response = get_data_from_url(request_query='streams/heading', access_token=access_token, data=data, auth_signature=oauth_signature, url='/api/v1/streams/heading', session_id=session_id)

                            if heading_update_response['status'] == 'OK':
                                response_data['heading'] = heading

                for ws in streams_clients[stream_uuid]:
                    ws.client.sendMessage(u'' + json.dumps(response_data))

            else:
                write_to_log(u'socket_open: client_data_status - ' + client_data_status + u', stream_data_status: ' + client_data_status)
        else:
            write_to_log(u'socket_open: access_token_stream_data_res - ' + access_token_stream_data_status + u', access_token_client_data_res: ' + access_token_client_data_status)

    def handleConnected(self):
        clients_pull.append(self)

    def handleClose(self):
        disconnected_client_id = int
        disconnected_from_stream_uuid = str

        for stream_uuid, array in streams_clients.items():
            for ws in array:
                if ws.client == self:
                    disconnected_client_id = ws.client_id
                    device_uuid = ws.device_uuid
                    session_id = ws.session_id
                    disconnected_from_stream_uuid = stream_uuid

                    if device_uuid:
                        # Create user/device signature
                        hash_object = hashlib.sha1(disconnected_client_id.encode('utf-8') + device_uuid.encode('utf-8'))
                        oauth_signature = hash_object.hexdigest()
                    else:
                        # Create web_user signature
                        hash_object = hashlib.sha1(
                            site_auth_login.encode('utf-8') + site_auth_pass.encode('utf-8') + str(disconnected_client_id).encode('utf-8'))
                        oauth_signature = hash_object.hexdigest()

                    # Get access token
                    access_token_res = get_access_token(request_query='streams/connect',
                                                        oauth_signature=oauth_signature)
                    if access_token_res['status'] == 'OK':
                        access_token = access_token_res['access_token']

                        change_connect_state_data = json.dumps({'uuid': stream_uuid, 'state': 'disconnect'}).encode('utf8')
                        change_connect_state_response = get_data_from_url(request_query='streams/connect',
                                                                          access_token=access_token,
                                                                          auth_signature=oauth_signature,
                                                                          data=change_connect_state_data,
                                                                          url='/api/v1/streams/connect', session_id=session_id)
                        if change_connect_state_response['status'] == 'OK':
                            write_to_log(u'disconnected: OK client_id - ' + str(
                                ws.client_id))
                        else:
                            change_connect_status = change_connect_state_response['status']
                            write_to_log(u'disconnected: ' + change_connect_status + u' client_id - ' + str(
                                ws.client_id))

                    array.remove(ws)

        for stream_client_id in stream_clients_ids:
            if stream_client_id == disconnected_client_id:
                stream_clients_ids.remove(disconnected_client_id)

        response_data = {}
        response_data['type'] = 'disconnected'
        response_data['client_id'] = disconnected_client_id
        response_data['stream_uuid'] = disconnected_from_stream_uuid
        response_data['stream_clients'] = stream_clients_ids

        for ws in streams_clients[disconnected_from_stream_uuid]:
            ws.client.sendMessage(u'' + json.dumps(response_data))


if __name__ == "__main__":

    parser = OptionParser(usage="usage: %prog [options]", version="%prog 1.0")
    parser.add_option("--host", default='', type='string', action="store", dest="host", help="hostname (localhost)")
    parser.add_option("--port", default=8000, type='int', action="store", dest="port", help="port (8000)")

    if VER >= 3:
        parser.add_option("--ssl", default=1, type='int', action="store", dest="ssl", help="ssl (1: on, 0: off (default))")
        parser.add_option("--cert", default='/www/ssl_cert/cert.pem', type='string',
                          action="store", dest="cert", help="cert (/www/ssl_cert/cert.pem)")
        parser.add_option("--ver", default=ssl.PROTOCOL_TLSv1, type=int, action="store", dest="ver", help="ssl version")
    else:
        parser.add_option("--ssl", default=0, type='int', action="store", dest="ssl", help="ssl (1: on, 0: off (default))")

    (options, args) = parser.parse_args()

    cls = Chat

    if options.ssl == 1:
        server = SSLWebSocketServer(options.host, options.port, cls, options.cert, options.cert, version=options.ver)
    else:
        server = WebSocketServer(options.host, options.port, cls)

    def close_sig_handler(signal, frame):
        server.close()
        sys.exit()

    def get_access_token(request_query, oauth_signature):
        req = Request('/api/v1/auth/access_token')
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

        req.add_header('Request-Type', request_query)
        req.add_header('Auth-Signature', auth_signature)
        req.add_header('Access-Token', access_token)
        req.add_header('Session-Id', session_id)
        json_data = json.loads(urlopen(req).read().decode())
        return json_data

    def write_to_log(data):
        datestr = time.strftime('%d%m%Y')
        f = open('/www/websocket_server/logs/log_' + datestr + '.log', 'a')
        timestr = time.strftime("[%H:%M]")
        f.write(timestr + ' ' + data + '\n')
        f.close()

    signal.signal(signal.SIGINT, close_sig_handler)

    server.serveforever()