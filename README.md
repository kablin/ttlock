Команды АПИ



Создать пользователя. На вход логин и пароль от учетной записи ттлока
/v1/create_user
{
"email" : "xxxx@rambler.ru",
"password" : "************"
}



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



Получить лог событий
/v1/get_lock_events
{
    "lock_id":"11743453",   // обязательное
    "personal": true   ,         // только события персонала
    "lock_record_type": "4" ,    // фильтр по типу    https://euopen.ttlock.com/document/doc?urlName=cloud%2FlockRecord%2FrecordTypeFromLockEn.html
    "record_type" : "12"        // фильтр по типу https://euopen.ttlock.com/document/doc?urlName=cloud%2FlockRecord%2FrecordTypeFromCloudEn.html
}


Получить лог событий версия 2
/v1/get_lock_events2
{
    "lock_ids":"11743453,11743453,11743453",   // обязательное
    "code": "1234",            // пин-код  только для типа 1
    "type" :"1"               // 1 -первый вход  2- входы персонала - 100 последних 3 -все данные 100 последних
    
}