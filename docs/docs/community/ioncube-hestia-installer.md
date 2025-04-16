#!/bin/bash

# 1. Instalar dependências
sudo apt update && sudo apt install -y nodejs npm git python3-venv

# 2. Criar estrutura do projeto
mkdir -p ~/assistente-ia/{public,config}
cd ~/assistente-ia

# 3. Instalar backend
cat > server.js <<'EOL'
const express = require('express');
const { exec } = require('child_process');
const app = express();
app.use(express.json());

const COMANDOS = {
  "instalar wordpress": "mkdir -p ~/public_html && wget https://br.wordpress.org/latest-pt_BR.zip && unzip latest-pt_BR.zip -d ~/public_html && echo 'WordPress instalado!'",
  "criar site": "read -p 'Nome do site: ' site && mkdir -p ~/public_html/$site && echo 'Site $site criado!'"
};

app.post('/comando', (req, res) => {
  const cmd = req.body.comando.toLowerCase();
  COMANDOS[cmd] ? exec(COMANDOS[cmd], (e,o,er) => res.send(e ? er : o)) : res.send("Comando não reconhecido");
});

app.listen(3000, '0.0.0.0', () => console.log('Servidor rodando!'));
EOL

# 4. Instalar frontend
cat > public/index.html <<'EOL'
<!DOCTYPE html>
<html>
<head>
  <title>Assistente</title>
  <script>
    async function enviar(comando) {
      const res = await fetch('/comando', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ comando })
      });
      document.getElementById('resposta').innerText = await res.text();
    }
    
    function iniciarVoz() {
      const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
      recognition.lang = 'pt-BR';
      recognition.onresult = (e) => enviar(e.results[0][0].transcript);
      recognition.start();
    }
  </script>
</head>
<body>
  <h1>Assistente de Comandos</h1>
  <button onclick="iniciarVoz()">🎤 Falar</button>
  <pre id="resposta"></pre>
</body>
</html>
EOL

# 5. Configurar npm
npm init -y && npm install express

# 6. Iniciar servidor (em background)
node server.js &

# 7. Criar acesso via Ngrok (se precisar de URL pública)
read -p "Quer disponibilizar online via Ngrok? (sim/não): " resposta
if [ "$resposta" = "sim" ]; then
  wget https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip
  unzip ngrok-stable-linux-amd64.zip
  ./ngrok http 3000 &
  echo "Acesse a URL abaixo:"
  curl -s http://localhost:4040/api/tunnels | grep -o 'https://[^"]*'
fi

echo "Instalação completa! Acesse http://$(curl -s ifconfig.me):3000"
