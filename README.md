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

   ```shell
   composer install
   ```

   ```shell
   http://127.0.0.1:8080/
   ```


## Dokumentacja API
### 1. Rejestracja nowej kolejki górskiej
**Endpoint:** `POST http://127.0.0.1:8080/api/coasters`

**Przykładowy request:**
```json
{
  "liczba_personelu": 16,
  "liczba_klientow": 60000,
  "pojemnosc_wagonu": 32,
  "predkosc": 1.2,
  "dl_trasy": 1800,
  "godziny_od": "08:00",
  "godziny_do": "16:00"
}
```

### 2. Rejestracja nowego wagonu
**Endpoint:** `POST http://127.0.0.1:8080/api/coasters/:coasterId/wagons`

**Przykładowy request:**
```json
{
  "ilosc_miejsc": 32,
  "predkosc_wagonu": 1.2
}
```

Jeśli w ustawieniach aplikacji (.env) poniższe wartości ustawione są na true:
```dotenv
CAPACITY_FROM_COASTER=true
SPEED_FROM_COASTER=true
```
to dane wprowadzone do wagonu nie mają znaczenia na monitorowanie kolejki, a pojemność wagonu/prędkość brane są z danych wprowadzonych w kolejce.

### 3. Usunięcie wagonu
**Endpoint:** `DELETE http://127.0.0.1:8080/api/coasters/:coasterId/wagons/:wagonId`

### 4. Zmiana danych kolejki
**Endpoint:** `PUT http://127.0.0.1:8080/api/coasters/:coasterId`

Zmienić można wszystkie dane poza długością trasy.

## Tryby działania
### Tryb deweloperski
- Rejestruje wszystkie typy logów
- Ogranicza konfliktowanie danych z wersją produkcyjną

### Tryb produkcyjny
- Rejestruje tylko logi typu `warning` oraz `error`
- Dane są odseparowane od środowiska deweloperskiego

## Monitorowanie i statystyki
Aplikacja udostępnia asynchroniczny serwis CLI do monitorowania systemu w czasie rzeczywistym.

   ```sh
   php spark monitor:start
   ```
Monitorowanie można zatrzymać za pomocą Ctrl+C lub z drugiego terminala uruchamiając komendę:

   ```sh
   php spark monitor:stop
   ```
