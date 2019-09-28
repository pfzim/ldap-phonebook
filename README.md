# LDAP phonebook
[:ru:](#корпоративный-телефонный-справочник-с-отображением-контактов-на-карте-офиса) [:us:](#corporate-phone-directory-with-contacts-displayed-on-the-office-map)

# Корпоративный телефонный справочник с отображением контактов на карте офиса

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

Карты хранятся в файлах `templ/map[1-5].png`

## Системные требования
- Apache
- MySQL
- PHP
- Active Directory (опционально)

Подключить модули расширения в php.ini или скомпилировать PHP с поддержкой LDAP
- `extension=php_ldap.dll`
- `extension=php_fileinfo.dll`

## Установка
- Открыть в браузере `install.php` и заполнить предлагаемые параметры
- Заменить изображения карт `templ/map[1-5].png` своими схемами

## Дополнительные настройки в inc.config.php (опционально)
Включить LDAP авторизацию:
```
define('PB_USE_LDAP_AUTH', 1);
define('LDAP_ADMIN_GROUP_DN', 'CN=Phonebook admin,OU=Admin Roles,OU=Groups,OU=Company,DC=domain,DC=local'); // Группа в AD с пользователями имеющими доступ на редактирование справочника
```
Изменить количество и названия карт:
```
define('PB_MAPS_COUNT', 5);
$map_names = array('Floor 1', 'Floor 3', 'Floor 6', 'Floor 14', 'Floor 25');
```

Изменить язык (cпасибо @Impuls2003):
```
define('APP_LANGUAGE', 'ru');
```


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

Maps are stored in the files `templ / map [1-5] .png`

## System requirements
- Apache
- MySQL
- PHP
- Active Directory (optional)

Plug-ins in php.ini or compile PHP with LDAP support
- `extension = php_ldap.dll`
- `extension = php_fileinfo.dll`

## Installation
- Open in the browser `install.php` and fill in the proposed parameters
- Replace the images of the maps `templ / map [1-5] .png` with their schemes


Dmitry V. Zimin <pfzim@mail.ru>
