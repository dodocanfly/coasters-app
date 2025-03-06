# coasters-app

```shell

git clone https://github.com/dodocanfly/coasters-app

composer install

docker compose -f docker-compose-dev.yml build

docker compose -f docker-compose-dev.yml up -d


POST http://localhost:8080/api/coasters
{
  "liczba_personelu": 4,
  "liczba_klientow": 400,
  "dl_trasy": 400,
  "godziny_od": "10:00",
  "godziny_do": "18:00"
}

POST http://localhost:8080/api/coasters/coaster_xxxxxx/wagons
{
    "ilosc_miejsc": 33,
    "predkosc_wagonu": 1.5
}


php spark monitor:start

php spark monitor:stop / Ctrl+C


```