import serial
import requests
import time


PORTA = "COM6"
BAUD = 9600


URL = "https://meuprojetophp-tya8.onrender.com/registrar.php" 

try:
    ser = serial.Serial(PORTA, BAUD, timeout=1)
except serial.SerialException as e:
    print(f"❌ ERRO: Nao foi possivel abrir a porta {PORTA}.")
    print("Verifique se o Arduino esta conectado e o IDE nao esta usando a porta.")
    exit()


print("🔗 Conectado ao Arduino!")
print("Digite o nome quando quiser iniciar a partida.\n")

while True:
    nome = input("Nome do jogador: ").strip()

    if nome == "":
        print("Nome vazio. Tente novamente.")
        continue

  
    ser.write((nome + "\n").encode())
    print(f"➡ Nome enviado ao Arduino: {nome}")
    print("Aguardando pontos...\n")


    while True:
        try:
            linha = ser.readline().decode().strip()
        except:
            continue

        if not linha:
            continue

        print("Arduino:", linha)
        
        if linha.startswith("FINAL_SCORE:"):
            try:
                _, _, dados_placar = linha.partition("FINAL_SCORE:")
                
                dados_placar = dados_placar.strip()
                
                nome_recebido, pontos = dados_placar.split(";")
                
                dados = {
                    "nome": nome_recebido.strip(),
                    "pontos": pontos.strip()
                }

                print("\n➡ Registrando no servidor:", dados)

                headers = {
                    "User-Agent": "Arduino-Python-Client/1.0"
                }
                
                r = requests.post(URL, data=dados, headers=headers, timeout=10)
                
                if r.status_code == 200:
                    print("✅ SUCESSO! Servidor respondeu:", r.text.strip(), "\n")
                else:
                    print(f"❌ Erro HTTP {r.status_code}. Servidor respondeu:", r.text.strip(), "\n")

            except Exception as e:
                print(f"❌ Erro inesperado ao processar ou enviar: {e}")

            break
