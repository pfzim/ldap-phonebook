# LDAP phonebook

Телефонный справочник из Active Directory

Импортирует данные из AD в базу MySQL, при следующей синхронизации данные уже добавленных контактов обновляются.

Функциональные возможности:
- Показать/скрыть контакт
- Указать расположение контакта на плане офиса. Одобно для ориентирования в большой организации
- Экспорт в .xml файл для использования в приложении [PhoneBook](https://github.com/pfzim/PhoneBook) для Windows

Карты хранятся в файлах templ/map[1-5].png





This service import users info from LDAP/AD to MySQL DB

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_0.png)

Show all contacts on map

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_1.png)

Show selected contact on map

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_2.png)

Dmitry V. Zimin <pfzim@mail.ru>
