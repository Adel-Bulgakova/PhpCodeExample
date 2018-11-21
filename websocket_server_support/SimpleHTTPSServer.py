import BaseHTTPServer, SimpleHTTPServer
import ssl

# openssl req -new -x509 -days 365 -nodes -out cert.pem -keyout cert.pem
httpd = BaseHTTPServer.HTTPServer(('', 443), SimpleHTTPServer.SimpleHTTPRequestHandler)
httpd.socket = ssl.wrap_socket(httpd.socket, server_side=True, certfile='/www/ssl_cert/cert.pem', keyfile='/www/ssl_cert/cert.pem', ssl_version=ssl.PROTOCOL_TLSv1)
httpd.serve_forever()