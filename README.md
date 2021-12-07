# LDAP phonebook
[:ru:](#корпоративный-телефонный-справочник-с-отображением-контактов-на-карте-офиса) [:us:](#corporate-phone-directory-with-contacts-displayed-on-the-office-map)  
[Development ветка](https://github.com/pfzim/ldap-phonebook/tree/dev)  
Вопросы предпочтительнее [задавать в Issues](https://github.com/pfzim/ldap-phonebook/issues?q=), а не по почте

# [Корпоративный телефонный справочник с отображением контактов на карте офиса](https://github.com/pfzim/ldap-phonebook)

Контакты делятся на два типа:
- Импортированные из AD
- Локальные

Импортированные контакты нельзя редактировать, их можно только скрывать из списка и указывать расположение на карте.
Все изменения нужно производить в AD, после чего провести повторную синхронизацию, при которой будут добавлены новые и обновлены существующие контакты.

Функциональные возможности:
- Импорт контактов и фото из AD
- Показать/скрыть любой контакт
- Добавлять, редактировать и удалять локальные контакты
- Указать расположение контакта на плане офиса. Удобно для ориентирования в большой компании
- Экспорт в .xml файл для использования в приложении [PhoneBook](https://github.com/pfzim/PhoneBook) для Windows
- Бекап всех контактов с координатами в XML
- Восстановление контактов из XML бекапа
- Скрытие всех контактов, которые были отключены в AD

Карты хранятся в файлах `templates/map[1-5].png`

## Системные требования
- Apache (nginx)
- MariaDB (MySQL)
- PHP
- Active Directory (опционально)
- memcaсhed (опционально)
- Kerberos (опционально)

Подключить модули расширения в php.ini или скомпилировать PHP с поддержкой LDAP
- `extension=php_ldap.dll`
- `extension=php_fileinfo.dll`

## Установка
- Открыть в браузере `install.php` и заполнить предлагаемые параметры
- Заменить изображения карт `templates/map[1-5].png` своими схемами

## Обновление
* Сделайте резервную копию базы данных и файлов.
* Переименуйте старую папку с файлами справочника.
* Распакуйте дистрибутив справочника в старое расположение
* Перенесите из старой версии файл inc.config.php, папку photos и карты из templ
* Запустите скрипт обновления открыв в браузере `upgrade.php`
* Скорее всего предварительно потребуется обновить конфигурационный файл `inc.config.php` добавив в него новые параметра по примеру из examples

## Дополнительные настройки в inc.config.php (опционально)
Для включения LDAP аутентификации в LDAP_ADMIN_GROUP_DN нужно указать группу AD через которую предоставляется доступ:
```
// Группа в AD с пользователями имеющими доступ на редактирование справочника
define('LDAP_ADMIN_GROUP_DN', 'CN=Phonebook admin,OU=Admin Roles,OU=Groups,OU=Company,DC=domain,DC=local');
```
Изменить количество и названия карт:
```
define('PB_MAPS_COUNT', 5);
$map_names = array('Floor 1', 'Floor 3', 'Floor 6', 'Floor 14', 'Floor 25');
```

Изменить язык (cпасибо [@Impuls2003](https://github.com/Impuls2003)):
```
define('APP_LANGUAGE', 'ru');
```

## Изменения в новых версиях
**10.10.2020**
- Параметры LDAP_HOST и LDAP_PORT заменены на LDAP_URI. Пример: `ldaps://dc-01 ldap://dc-02:389`
- При обнолении пароли у учётных записей будут заменены на 'admin', т.к. функция PASSWORD больше не поддерживается MySQL. Их требуется сменить.
- Теперь аутентифакация LDAP и локальная работают параллельно. Для активации LDAP аутентификации в LDAP_ADMIN_GROUP_DN нужно указать группу доступа AD.

This service import users info from LDAP/AD to MySQL DB

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_0.png)

Show all contacts on map

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_1.png)

Show selected contact on map

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_2.png)

Installation

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_3.png)

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_4.png)

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_5.png)

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_6.png)

![screenshot](https://raw.githubusercontent.com/pfzim/ldap-phonebook/master/other/screenshot_7.png)

---

# Corporate phone directory with contacts displayed on the office map

Contacts are divided into two types:
- Imported from AD
- Local

Imported contacts can not be edited, you can only hide them from the list and specify the location on the map.
All that needs to be done in AD, then re-synchronize, which will be added new and updated.

Functionality:
- Import contacts and photos from AD
- Show / hide any contact
- Add, edit and delete local contacts
- Indicate the location of the contact on the office plan. Convenient for orientation in a large company
- Export to an .xml file for use in the [Phonebook](https://github.com/pfzim/PhoneBook) application for Windows
- Backup all contacts with coordinates to XML file
- Restore all contacts from XML file
- Hide all contacts that was disabled in AD

Maps are stored in the files `templates/map[1-5].png`

## System requirements
- Apache
- MariaDB (MySQL)
- PHP
- Active Directory (optional)

Plug-ins in php.ini or compile PHP with LDAP support
- `extension = php_ldap.dll`
- `extension = php_fileinfo.dll`

## Installation
- Open in the browser `install.php` and fill in the proposed parameters
- Replace the images of the maps `templates/map[1-5].png` with their schemes


Dmitry V. Zimin <pfzim@mail.ru> Ask in [Issues](https://github.com/pfzim/ldap-phonebook/issues?q=) before mail me.
