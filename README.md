# Application features
- User registration
- User login
- After registration user gets default categories
- Categories CRUD
- Transactions CRUD (every transaction has category and transaction can be income or expense)
- Transactions can be filtered by date, category, type (income or expense), min amount, max amount and when transaction will become active
- Transaction data aggregation endpoint with sum of income and expense transactions, number of income and expense transaction and balance. Endpoint can be filtered by date and categories


# How to install application
1. Clone this repository `git clone https://github.com/tomopongrac/home-budget.git`
2. `cd` into the project directory `cd home-budget`
3. Run `composer install`
4. Create .env.local file `touch .env.local`
5. Add `DATABASE_URL` to .env.local file
6. Run `php ./bin/console doctrine:database:create`
7. Run `php ./bin/console doctrine:migrations:migrate`
8. Since application use JWT for authentication, you need to generate public and private keys. Run `php ./bin/console lexik:jwt:generate-keypair`
9. `symfony server:start`

# Api Documentation
1. `symfony open:local`
2. open [http:8000/api/doc](http:8000/api/doc)

# How to run tests
1. Add `DATABASE_URL` to .env.test.local file
2. To create test database run `php ./bin/console doctrine:database:create --env=test`
3. To run tests run `php bin/phpunit`

# How to run static analysis
1. `composer phpstan`

# How to run code style fixer
1. `composer cs-fixer-fix`
