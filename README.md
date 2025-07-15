<h1>Команды АПИ</h1>
<p>&nbsp;</p>
<p><strong>&nbsp; api/v1/create_user</strong></p>
<p>Создать пользователя. На вход логин и пароль от учетной записи ттлока<br /><br />{<br />"email" : "xxxx@rambler.ru",<br />"password" : "************"<br />}</p>
<hr />
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/get_token</strong></p>
<p>Получение токена авторозации<br /><br /></p>
<p>{<br />"email":"qqq@qq.qq",<br />"password":"123456"</p>
<p>}<br />В ответ bearer токен авторизации для всех остальных запросов</p>
<p>&nbsp;</p>
<hr />
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/set_callback</strong></p>
<p>Установить коллбэк для пользователя<br /><br /></p>
<p>{<br />"callback":"http://xxx.com/callback"<br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<hr />
<p>&nbsp;</p>
<h3>Запросы к апи которые возвращают job_id с уникальным номером запроса. После выполнения запроса к облаку TTLOCK вызвается коллбэк пользователя куда передается job_id и результат запроса</h3>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/get_lock_list</strong></p>
<p><strong>*</strong> Получить список всех замков пользователя<br /><br /></p>
<p><strong>&nbsp;api/v1/open_lock</strong></p>
<p><strong>* </strong>Открыть замок</p>
<p>{<br />"lock_id":"12345"<br />}</p>
<p>&nbsp;</p>
<p><strong>api/v1/add_code_to_lock</strong></p>
<p><strong>**</strong> Добавить код в замок. В случае неудачи запускается заново через 20 минут в течение 5 раз.<br />{<br />"begin":"timestamp",<br />"end":"timestamp",<br />"code": "1234",<br />"lock_id" :"1234566"<br />}</p>
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/delete_code_from_lock</strong></p>
<p><strong>*</strong> Удалить код из замка В случае неудачи запускается заново через 20 минут в течение 5 раз.<br />{<br />"lock_id":"12345",<br />"code_id":"123456",<br />}</p>
<p>&nbsp;</p>
<p><strong>api/v1/set_lock_passage_mode_on</strong></p>
<p><strong>*</strong> Включить режим свободного прохода<br /><br /></p>
<p>{<br />"lock_id":"12345",<br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/set_lock_passage_mode_off</strong></p>
<p><strong>*</strong> Выключить режим свободного прохода<br />{<br />"lock_id":"12345",<br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<hr />
<h3>Запросы к АПИ, которые выполняются сразу</h3>
<p><strong>api/v1/get_lock_events</strong></p>
<p><strong>*</strong> Получить лог событий<br /><br />{<br /> "lock_id":"11743453", // обязательное<br /> "personal": true , // только события персонала<br /> "lock_record_type": "4" , // фильтр по типу<a href="https://euopen.ttlock.com/document/doc?urlName=cloud%2FlockRecord%2FrecordTypeFromLockEn.html">_список_</a><br /> "record_type" : "12" // фильтр по типу <a href="https://euopen.ttlock.com/document/doc?urlName=cloud%2FlockRecord%2FrecordTypeFromCloudEn.html">_список_</a><br />}</p>
<p>&nbsp;</p>
<p><strong>api/v1/get_lock_events2</strong></p>
<p><strong>*</strong> Получить лог событий версия 2<br /><br />{<br /> "lock_ids":"11743453,11743453,11743453", // обязательное<br /> "code": "1234", // пин-код только для типа 1<br /> "type" :"1" // 1 -первый вход 2- входы персонала - 100 последних 3 -все данные 100 последних<br /> <br />}</p>
<p><strong>&nbsp;api/v1/get_events_by_code</strong></p>
<p><strong>*</strong> Получить лог событий по коду<br /><br />{<br />"lock_id" : "11743453",<br />"code" : "1234"<br />}<br />На вход, ид замка и код. Возвращает последние 100 событий по этому коду на данном замке. Ответ сразу, без коллбэка</p>
<p>&nbsp;</p>
<p><strong>api/v1/add_code_packet</strong></p>
<p>Добавить пакет кодов<br /><br />{<br />"codes_count" : "100",<br />"" : "Y-m-d H:i:s" //формат важен!<br />}</p>
<p>&nbsp;</p>
<p><strong>api/v1/get_codes_count</strong></p>
<p>Получить количество доступных кодов<br /><br /></p>
<p>&nbsp;</p>
<p>* - требуется наличие активных кодов<br />** - уменьшает счетчик активных кодов на 1</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<hr />
<h1>Общая информация</h1>
<p>Таблицы логов</p>
<h2>lock_api_logs - лог вызванных методов АПИ</h2>
<h2>lock_events - лог событий от облака TTLOCK</h2>
<h2>lock_options,&nbsp;lock_values - значения параметров замков</h2>
<h2>lock_values_logs - лог изменений значений параметров</h2>
<hr />
<p>&nbsp;</p>
<p>Обновление токенов производится автоматически при неудачном запросе к облаку.</p>
<p>&nbsp;</p>
<p>Шедулеры</p>
<p>GetLockEventsSchedule - каждые 6 часов - принудительный запрос событий замка</p>
<p>GetLockListSchedule - каждый час - получение параметров замков<br /><br />DeleteLockPinCodesSchedule - ежедневно, удаление страрых ключей</p>
<p>&nbsp;</p>
<p>&nbsp;</p>



<hr />
<hr />



<p>Использование API</p>
<p>Все запросы вызываются методом POST</p>

<p>1.  Создать пользователя   - api/v1/create_user </p>
<p>2.  Получить Bearer токен авторизации, который используется во всех остальных запросах   -  api/v1/get_token </p>
<p>3.  Установить адрес обратного вызова, на который будет приходить ответ от API при работе с замками   - api/v1/set_callback </p>
<p>4.  Выполнять остальные запросы к апи</p>