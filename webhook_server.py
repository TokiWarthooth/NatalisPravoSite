#!/usr/bin/env python3
"""
Простой webhook сервер для автоматического деплоя
Запускается на сервере и слушает запросы от GitHub
"""

from http.server import HTTPServer, BaseHTTPRequestHandler
import subprocess
import json
import hmac
import hashlib

# Секретный токен для проверки (установите свой)
WEBHOOK_SECRET = "your_secret_token_here"
PORT = 9000

class WebhookHandler(BaseHTTPRequestHandler):
    def do_POST(self):
        if self.path == '/deploy':
            content_length = int(self.headers['Content-Length'])
            post_data = self.rfile.read(content_length)
            
            # Проверка подписи (опционально)
            signature = self.headers.get('X-Hub-Signature-256', '')
            
            try:
                # Запуск скрипта деплоя
                print("🚀 Webhook received, starting deployment...")
                result = subprocess.run(
                    ['/bin/bash', '/var/www/NatalisPravoSite/deploy.sh'],
                    capture_output=True,
                    text=True,
                    timeout=300
                )
                
                self.send_response(200)
                self.send_header('Content-type', 'application/json')
                self.end_headers()
                
                response = {
                    'status': 'success',
                    'message': 'Deployment started',
                    'output': result.stdout
                }
                self.wfile.write(json.dumps(response).encode())
                
                print("✅ Deployment completed")
                print(result.stdout)
                
            except Exception as e:
                print(f"❌ Error: {e}")
                self.send_response(500)
                self.send_header('Content-type', 'application/json')
                self.end_headers()
                
                response = {
                    'status': 'error',
                    'message': str(e)
                }
                self.wfile.write(json.dumps(response).encode())
        else:
            self.send_response(404)
            self.end_headers()
    
    def log_message(self, format, *args):
        print(f"[{self.log_date_time_string()}] {format % args}")

if __name__ == '__main__':
    server = HTTPServer(('0.0.0.0', PORT), WebhookHandler)
    print(f"🎯 Webhook server running on port {PORT}")
    print(f"📡 Listening for POST requests on /deploy")
    server.serve_forever()
