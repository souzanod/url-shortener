URL SHORTENER
==================

## Build docker images

```
docker-compose build
```

## Run application

```
docker-compose up -d
```

## Execute composer install

```
docker-compose run php composer install
```

## Execute migrations

```
docker-compose run php bin/console doctrine:migrations:migrate
```

## Execute unit tests
```
docker-compose run php composer tests
```

## Access application from browser

http://localhost:8080