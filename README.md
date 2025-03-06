# System Kolejek Górskich - API

## Opis projektu

System Kolejek Górskich to API do zarządzania kolejkami górskimi oraz przypisanymi do nich wagonami. Pozwala na rejestrację kolejek, dodawanie wagonów oraz monitorowanie dostępności personelu i wagonów w kontekście liczby klientów. Aplikacja działa w dwóch trybach: deweloperskim oraz produkcyjnym.

## Technologie
- **PHP 8.0+**
- **CodeIgniter 4**
- **Redis**
- **Docker Compose**
- **Nginx + PHP-FPM**

## Funkcjonalności
- Rejestracja nowych kolejek górskich
- Dodawanie i usuwanie wagonów
- Modyfikacja danych kolejek
- Monitorowanie dostępności personelu i wagonów
- Tryby działania: deweloperski i produkcyjny
- Obsługa logowania zdarzeń i statystyk w czasie rzeczywistym

## Instalacja i uruchomienie
### Wymagania systemowe
- Docker + Docker Compose
- PHP 8.0+
- Redis

### Instalacja
1. **Sklonuj repozytorium:**
   ```sh
   git clone https://github.com/dodocanfly/coasters-app
   cd coasters-app
   ```
2. **Uruchom kontenery Docker:**
   ```sh
   docker compose -f docker-compose-dev.yml up -d
   lub
   docker compose -f docker-compose-prod.yml up -d
   ```

5. **Start serwera lokalnego:**
   ```sh
   php spark serve
   ```

## Dokumentacja API
### 1. Rejestracja nowej kolejki górskiej
**Endpoint:** `POST /api/coasters`

**Przykładowe żądanie:**
```json
{
  "liczba_personelu": 16,
  "liczba_klientow": 60000,
  "dl_trasy": 1800,
  "godziny_od": "08:00",
  "godziny_do": "16:00"
}
```

### 2. Rejestracja nowego wagonu
**Endpoint:** `POST /api/coasters/:coasterId/wagons`

**Przykładowe żądanie:**
```json
{
  "ilosc_miejsc": 32,
  "predkosc_wagonu": 1.2
}
```

### 3. Usunięcie wagonu
**Endpoint:** `DELETE /api/coasters/:coasterId/wagons/:wagonId`

### 4. Zmiana danych kolejki
**Endpoint:** `PUT /api/coasters/:coasterId`

Zmienić można wszystkie dane poza długością trasy.

## Tryby działania
### Tryb deweloperski
- Rejestruje wszystkie typy logów
- Ogranicza konfliktowanie danych z wersją produkcyjną

### Tryb produkcyjny
- Rejestruje tylko logi typu `warning` oraz `error`
- Dane są odseparowane od środowiska deweloperskiego

## Monitorowanie i statystyki
Aplikacja udostępnia asynchroniczny serwis CLI do monitorowania systemu w czasie rzeczywistym. Można użyć np. `ReactPHP` do obsługi Redis w trybie asynchronicznym.

   ```sh
   php spark monitor:start
   ```
Monitorowanie można zatrzymać za pomocą Ctrl+C lub z drugiego terminala uruchamiając komendę:

   ```sh
   php spark monitor:stop
   ```
