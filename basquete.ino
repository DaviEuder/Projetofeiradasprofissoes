
String jogador = "";
int pontos = 0;
long duracao;
int distancia;
bool partidaEmAndamento = false;
unsigned long inicioPartida;
const unsigned long DURACAO = 15000; 

// ------------------------------------------------------------------
// VARIÁVEIS DE OTIMIZAÇÃO DE VELOCIDADE (AJUSTE CRÍTICO)
// ------------------------------------------------------------------
unsigned long ultimoAcerto = 0;
// REDUZIDO para 50ms: Permite que o sensor conte novamente 50ms após o último acerto. 
// Isso aumenta a velocidade de detecção, evitando que a próxima bola seja ignorada.
const unsigned long COOLDOWN_PONTO = 50; 
// ------------------------------------------------------------------

void setup() {
  Serial.begin(9600);
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);

  // Define um limiar de distância para o ultrassônico.
  // Se você tiver problemas com a bola pequena, tente aumentar este valor (ex: de 10 para 15 ou 20).
  Serial.println("Distância máxima para cesta (em cm) é 10.");
  Serial.println("Digite o nome do jogador e pressione Enter:");
}

void loop() {

 
  if (!partidaEmAndamento && Serial.available()) {
    jogador = Serial.readStringUntil('\n');
    jogador.trim(); 

    if (jogador.length() > 0) {
      pontos = 0;
      partidaEmAndamento = true;
      inicioPartida = millis();
      Serial.println("----------------------------------");
      Serial.println("Partida iniciada para: " + jogador);
    }
  }

  if (partidaEmAndamento) {

    // --- Medição Ultrassônica ---
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);

    duracao = pulseIn(echoPin, HIGH, 30000);
    distancia = duracao * 0.034 / 2;
    // --- Fim da Medição Ultrassônica ---

    // Detecta cesta E VERIFICA O COOLDOWN
  
    if (distancia < 10 && distancia > 0) { 
        
      // LÓGICA DE COOLDOWN (DEBOUNCE)
      if (millis() - ultimoAcerto > COOLDOWN_PONTO) {
          
        pontos++;
        ultimoAcerto = millis(); // Reseta o cronômetro do último acerto
        Serial.println("Cesta! Pontos: " + String(pontos));
      }
    }

    // Final da partida (Lógica inalterada)
    if (millis() - inicioPartida >= DURACAO) {
      partidaEmAndamento = false;

      Serial.println("----------------------------------");
      Serial.println("FIM_PARTIDA");

      Serial.print("FINAL_SCORE:");
      Serial.print(jogador);
      Serial.print(";");
      Serial.println(pontos);
      
      Serial.println("----------------------------------");
      Serial.println("Digite o nome do proximo jogador:");
    }
  }
}
