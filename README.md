
## Transaction Processing System API

### Prerequisites

- PHP v8.3

### Start up

To start project, perform the following steps in the order

- Clone the repository by running the command
- git clone 'https://github.com/Geoslim/wearecheck-transaction-api.git'
- cd wearecheck-transaction-api
- Run `composer install`
- Run `cp .env.example .env`
- Fill your configuration settings in the '.env' file you created above
- Create database 
- Run `php artisan key:generate`
- Run `php artisan migrate`

### Usage
- Application utilizes Laravel Sanctum for authentication. So make use of the token generated at sign up / login.
- Include token as bearer token
- Make use of the following endpoints to test the application
#### Assuming baseUrl: http://127.0.0.1:8000

- SIGN UP
```http
POST: {{baseUrl}}/api/v1/auth/register
````
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "Secret@123"
}
```
- LOGIN
```http
POST: {{baseUrl}}/api/v1/auth/login
````
```json
{
    "email": "john@example.com",
    "password": "Secret@123"
}
```

- FETCH WALLET BALANCE
```http
GET: {{baseUrl}}/api/v1/wallet
Accept: application/json
Authorization: ••••••
```
- CREATE TRANSACTION (CREDIT)
```http
POST: {{baseUrl}}/api/v1/transactions/create
Accept: application/json
Authorization: ••••••
````
```json
{
    "type": "credit",
    "amount": "500",
    "reference": "478990o3pkjn2u4yu28iojnk4" // any random reference of choice
}
```
- CREATE TRANSACTION (DEBIT)
```http
POST: {{baseUrl}}/api/v1/transactions/create
Accept: application/json
Authorization: ••••••
````
```json
{
    "type": "debit",
    "amount": "500"
}
```
### Testing
```bash
php artisan test
```

## Improvements
These are some considerations to scale this system and improve its
architecture for production use.

- We can move transaction processing into a background job queue to handle high-volume requests. To improve the user experience by reducing response time.
- We can cache frequently accessed data (user wallet balances, transaction record etc) using Redis for example to reduce the load on the database and improve response time. Update the cache when balances change.
- On the infrastructure level, the use of load balancing would help distribute traffic.
- Obviously credit and debit won't be handled by the same endpoint to ensure separation of concerns
- Credits will most likely be handled by webhooks and the webhook will have some hashing mechanism to ensure the authenticity of the source
- Rate-limiting to throttle the frequency of requests to prevent abuse.

### Other Considerations
- Transaction Pins to authorize debits. And throttling the pin to prevent abuse, such as brute-force attacks
- Database indexes for faster queries where required
- Properly crafting a retry logic for failed debits and the appropriate ways to carry out refunds.
- Implementing backup third party providers to ensure transactions are still processed in the event a provider goes down
- I personally like using uuid or ulids as the primary key for sensitive tables like user and wallets, The harder the id's are to guess the harder it is for malicious attempts to manipulate rows in the case of a breach
- Appropriate Communication (error feedback & notifications)
- Proper monitoring and logging is essential to track all processes
