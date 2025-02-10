# IPvX Interface
## Project status
[![pipeline status](https://gitlab.com/ipvx/interface/badges/master/pipeline.svg)](https://gitlab.com/ipvx/interface/-/commits/master)
[![coverage report](https://gitlab.com/ipvx/interface/badges/master/coverage.svg)](https://gitlab.com/ipvx/interface/-/commits/master)
[![Latest Release](https://gitlab.com/ipvx/interface/-/badges/release.svg)](https://gitlab.com/ipvx/interface/-/releases)
## Code style enforcement
### Run the PHP code style fixer
Format the application PHP code in accordance to the required code style:
```
php tools/php-cs-fixer/vendor/bin/php-cs-fixer fix app
```

### Install commit linter hooks
Install hooks using husky (only needs to be done once after the project was cloned):
```
npx husky install
```
## Docker test environment
### Build the application

Build the custom Docker images required to run the application:
```
docker-compose build --no-cache
```

### Start the application

Launch the Docker containers required to run the application:
```
docker-compose up -d
```

### Stop the application

Stop and delete the Docker containers associated to the application:
```
docker-compose down
```
