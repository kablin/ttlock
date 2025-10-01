<h1>Команды АПИ</h1>
<p>&nbsp;</p>
<p><strong>&nbsp; api/v1/create_user</strong></p>
<p>Создать пользователя. На вход логин и пароль от учетной записи ттлока. source-текстовое поле, название системы из которой создается пользователь.</p>
<p>{<br />"email" : "xxxx@rambler.ru",<br />"password" : "************",<br />"source":"bitrix"<br />}</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "true",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'error" : "0",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Пользователь успешно создан"</span><br />}</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>&nbsp;</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'error" : "1",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не удалось залогиниться&nbsp; в облако TTLOCK"</span><br />}</p>
<p>&nbsp;</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'error" : "2",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не получилось запистать данные для авторизации в TTLOCK"</span><br />}</p>
<p>&nbsp;</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'error" : "3",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Неизвестная ошибка"</span><br />}</p>
<hr />
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/get_token</strong></p>
<p>Получение токена авторозации<br /><br /></p>
<p>{<br />"email":"qqq@qq.qq",<br />"password":"123456"</p>
<p>}<br />В ответ bearer токен авторизации для всех остальных запросов</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "true",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'token" : "xxxxxxx",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "user_id" : "123"</span><br />}</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>&nbsp;</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Неизвестная ошибка"</span><br />}</p>
<p>&nbsp;</p>
<hr />
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/set_callback</strong></p>
<p>Установить коллбэк для пользователя<br /><br /></p>
<p>{<br />"callback":"http://xxx.com/callback"<br />}</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "true",</span><br /><br />}</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Неизвестная ошибка"</span><br />}</p>
<p>&nbsp;</p>
<hr />
<p>&nbsp;</p>
<h3>Запросы к апи которые возвращают job_id с уникальным номером запроса. После выполнения запроса к облаку TTLOCK вызвается коллбэк пользователя куда передается job_id и результат запроса. Во всех этих запросах есть необязательное поле tag, куда можно записать произвольный json, и это поле будет возвращено с результатом</h3>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/get_lock_list</strong></p>
<p><strong>*</strong> Получить список всех замков пользователя<br /><br /></p>
<p>{<br />"tag":{}<br />}</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span></p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br />"status'": "true",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'tag" : {},</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "method" : "get_lock_list",<br /> 'data" : {//ответ ttlock},</span></p>
<p><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/open_lock</strong></p>
<p><strong>* </strong>Открыть замок</p>
<p>{<br />"lock_id":"12345",<br />"tag":{}<br />}</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br />"status'": "true",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'tag" : {},</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "method" : "open_lock",<br /> 'data" : {//ответ ttlock},</span><br /><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не указан lock_id"</span><br />}</p>
<p>&nbsp;</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br /> 'tag" : {},</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",<br /> "method" : "open_lock",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Неизвестный замок"</span><br />}</p>
<p>&nbsp;</p>
<p><strong>api/v1/add_code_to_lock</strong></p>
<p><strong>**</strong> Добавить код в замок. В случае неудачи запускается заново через 20 минут в течение 5 раз.&nbsp; ВРЕМЯ <strong>UTC</strong><br />{<br />"begin":"2025-08-28 15:43",<br />"end":"2025-08-28 15:43",<br />"code": "1234",<br />"code_name": "Персонал",<br />"lock_id" :"1234566",<br />"tag":{}<br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br />"status'": "true",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'tag" : {},</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "method" : "add_code_to_lock",<br /> 'data" : {//ответ ttlock},<br /> "msg" : "Ключ успешно загружен" </span><br /><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не указан lock_id" || "Не указан code"</span><br />}</p>
<p>&nbsp;</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br /> 'tag" : {},<br /> "method" : "add_code_to_lock",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Неизвестный замок"</span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br /> 'tag" : {},<br /> "method" : "add_code_to_lock",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",<br />"codes_error'": "true",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Нет оплаченного пакета кодов" || "Окончилась дата действия пакета кодов" || "Закончился пакет кодов"</span><br />}</p>
<p>&nbsp;</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br /> 'tag" : {},<br /> "method" : "add_code_to_lock",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Ошибка загрузки ключа. Количество попыток исчерпано." || Ошибка загрузки ключа. Следеющая попытка загрузки ключа чере 20 минут" </span><br />}&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/delete_code_from_lock</strong></p>
<p><strong>*</strong> Удалить код из замка В случае неудачи запускается заново через 20 минут в течение 5 раз.<br />{<br />"lock_id":"12345",<br />"code_id":"123456",<br />"tag":{}<br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br />"status'": "true",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'tag" : {},</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "method" : "delete_code_from_lock",<br /> 'data" : {//ответ ttlock},<br /> "msg": "Ключ успешно удален"</span><br /><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не указан lock_id" || "Не указан code_id"</span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br /> 'tag" : {},<br /> "method" : "add_code_to_lock",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Ошибка удаления ключа. Количество попыток исчерпано." || Ошибка удаления ключа. Следеющая попытка удвления ключа чере 20 минут" || "Неизвестный замок"</span><br />}&nbsp;</p>
<p>&nbsp;</p>
<p><strong>api/v1/set_lock_passage_mode_on</strong></p>
<p><strong>*</strong> Включить режим свободного прохода<br /><br /></p>
<p>{<br />"lock_id":"12345",<br />"tag":{}<br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br />"status'": "true",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'tag" : {},</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "method" : "set_lock_passage_mode_on",<br /> 'data" : {//ответ ttlock},<br /> "msg": "Режим свободного прохода включен"</span><br /><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не указан lock_id" </span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br /> 'tag" : {},<br /> "method" : "set_lock_passage_mode_on",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Неизвестный замок"</span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/set_lock_passage_mode_off</strong></p>
<p><strong>*</strong> Выключить режим свободного прохода<br />{<br />"lock_id":"12345",<br />"tag":{}<br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br />"status'": "true",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> 'tag" : {},</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "method" : "set_lock_passage_mode_off",<br /> 'data" : {//ответ ttlock},<br /> "msg": "Режим свободного прохода выключен"</span><br /><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не указан lock_id" </span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"job'": "xxxxxxx",<br /> 'tag" : {},<br /> "method" : "set_lock_passage_mode_off",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Неизвестный замок"</span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<hr />
<h3>Запросы к АПИ, которые выполняются сразу</h3>
<p><strong>api/v1/get_lock_events</strong></p>
<p><strong>*</strong> Получить лог событий<br /><br />{<br /> "lock_id":"11743453", // обязательное<br /> "personal": true , // только события персонала<br /> "lock_record_type": "4" , // фильтр по типу<a href="https://euopen.ttlock.com/document/doc?urlName=cloud%2FlockRecord%2FrecordTypeFromLockEn.html">_список_</a><br /> "record_type" : "12" // фильтр по типу <a href="https://euopen.ttlock.com/document/doc?urlName=cloud%2FlockRecord%2FrecordTypeFromCloudEn.html">_список_</a><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>[{</p>
<p><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"id":"..." ,<br />"lock_id":"..."<br />"record_type_from_lock":"...",<br />"record_type":"...",<br /> "success":"...",<br />"username":"...",<br />"keyboard_pwd":"...",<br />"lock_date":"..."</span></p>
<p>},]</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не указан lock_id" || "Неизвестный замок"</span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>api/v1/get_lock_events2</strong></p>
<p><strong>*</strong> Получить лог событий версия 2<br /><br />{<br /> "lock_ids":"11743453,11743453,11743453", // обязательное<br /> "code": "1234", // пин-код только для типа 1<br /> "type" :"1" // 1 -первый вход 2- входы персонала - 100 последних 3 -все данные 100 последних<br /> <br />}</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>[{</p>
<p><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"id":"..." ,<br />"lock_id":"..."<br />"record_type_from_lock":"...",<br />"record_type":"...",<br /> "success":"...",<br />"username":"...",<br />"keyboard_pwd":"...",<br />"lock_date":"..."</span></p>
<p>},]</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не указан lock_id" || "Неизвестный замок" || "Не указан type" || "Не указан code"</span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>&nbsp;api/v1/get_events_by_code</strong></p>
<p><strong>*</strong> Получить лог событий по коду<br /><br />{<br />"lock_id" : "11743453",<br />"code" : "1234"<br />}<br />На вход, ид замка и код. Возвращает последние 100 событий по этому коду на данном замке. Ответ сразу, без коллбэка</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>[{</p>
<p><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"id":"..." ,<br />"lock_id":"..."<br />"record_type_from_lock":"...",<br />"record_type":"...",<br /> "success":"...",<br />"username":"...",<br />"keyboard_pwd":"...",<br />"lock_date":"..."</span></p>
<p>},]</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не указан lock_id" || "Неизвестный замок" || "Не указан type" || "Не указан code"</span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>api/v1/add_code_packet</strong></p>
<p>Добавить пакет кодов<br /><br />{<br />"codes_count" : "100",<br />"" : "Y-m-d H:i:s" //формат важен!<br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{</p>
<p><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status":true,<br />"msg":"Пакет кодов успешно добавлен"<br />"codes_count":"123"<br />"expired_at":"2025-08-28 15:43"<br /></span>}</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не указан codes_count" || "codes_count не число" || "Не указан expired_at" || "expired_at неверный формат даты"</span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>api/v1/set_code_packet</strong></p>
<p>Установить пакет кодов,&nbsp; используется для разработки<br /><br />{<br />"codes_count" : "100",<br />"" : "Y-m-d H:i:s" //формат важен!<br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{</p>
<p><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status":true,<br />"codes_count":"123"<br />"msg":"Пакет кодов успешно установлен"<br />"expired_at":"2025-08-28 15:43"<br /></span>}</p>
<p>&nbsp;</p>
<p><span style="text-decoration: underline;">Ошибки</span>:</p>
<p>{<br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status'": "false",</span><br /><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;"> "msg" : "Не указан codes_count" || "codes_count не число" || "Не указан expired_at" || "expired_at неверный формат даты"</span><br />}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>api/v1/get_codes_count</strong></p>
<p>Получить количество доступных кодов<br /><br /></p>
<p><span style="text-decoration: underline;">Ответ</span>:</p>
<p>{</p>
<p><span class="selectable-text copyable-text xkrh14z" style="white-space: pre-wrap;">"status":true,<br />"codes_count":"123"<br />"expired_at":"2025-08-28 15:43"<br /></span>}</p>
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
<hr /><hr />
<p>Использование API</p>
<p>Все запросы вызываются методом POST</p>
<p>1. Создать пользователя - api/v1/create_user</p>
<p>2. Получить Bearer токен авторизации, который используется во всех остальных запросах - api/v1/get_token</p>
<p>3. Установить адрес обратного вызова, на который будет приходить ответ от API при работе с замками - api/v1/set_callback</p>
<p>4. Выполнять остальные запросы к апи</p>




 apt-get update
 apt-get install php8.2

apt-get install php8.2-mbstring
