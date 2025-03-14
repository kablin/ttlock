Команды АПИ


Получение токена авторозации
/v1/get_token

{
"email":"qqq@qq.qq",
"password":"123456"

}
В ответ bearer токен авторизации для всех остальных запросов






Запросы к апи возвращают job_id уникальным номером запросы. После выполнения запросы вызвается коллбэк пользователя куда передается результат


Установить коллбэк для пользователя
api/v1/set_callback

{
"callback":"http://xxx.com/callback"
}





Установить/изменить логин/пароль от ttlock
api/v1/create_credential

{
"user":"qqq@qq.qq",
"password":"123456",
}







Получить список всех замков пользователя
api/v1/get_lock_list




Добавить код в замок
api/v1/add_code_to_lock

{
"begin":"timestamp",
"end":"timestamp",
"code": "1234",
"lock_id" :"1234566"
}




Удалить код из замка
api/v1/delete_code_from_lock

{
"lock_id":"12345",
"code_id":"123456",
}



Включить режим свободного прохода
api/v1/set_lock_passage_mode_on

{
"lock_id":"12345",
}




Выключить режим свободного прохода
/v1/set_lock_passage_mode_off

{
"lock_id":"12345",
}
