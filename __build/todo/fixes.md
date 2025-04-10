Target class [role] does not exist. 

 Request
GET /player/stats 
no body data


Application
Routing

controller

App\Http\Controllers\PlayerStatsController@index

route name

player.stats

middleware

web, auth, verified, role:player

Database Queries

mysql (8.1 ms)

select * from `sessions` where `id` = 'dNauYqlorJaEvzc3L5n3vV6vEjpwHf672gLGssQe' limit 1

mysql (0.57 ms)

select * from `users` where `id` = 693 limit 1

