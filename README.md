# modx-evoCaptcha
MODX Evo cpatha for MODX Revo

Для задания списка символов используется настройка с ключом **evocaptcha.words**  
Параметры капчи (высота, ширина) задаются при вызове FormIt в опции **evocaptcha** в виде json  

#### Ключи для параметров:
 - **width** - ширина капчи. По умолчанию **148**
 - **height** - высота капчи. По умолчанию **60**
 - **sessionPrefix** - префикс сессии для использования капчи в разных формах на одной странице. По умолчанию **vericode** или **submitVar** от **FormIt**, если указан
 - **noisesDir** - путь к папке, где хранятся бэкграунды капчи. Изображения в папке имеют вид **noise<цифра>.jpg**. По умолчанию **/assets/components/evocaptcha/noises**
 - **ttfDir** - путь к папке, где хранится шрифт капчи. По умолчанию **/assets/components/evocaptcha/ttf**
 - **connector** - путь к php файлу генерирующему изображение, По умолчанию **/assets/components/evocaptcha/connector.php**

#### Для подключения к **FormIt**, необходимо прописать:
 - preHooks=`evocaptcha`
 - customValidators=`evocaptcha`
 - Для поля куда вводится код прописать валидатор - **<поле>:evocaptcha**

#### Доступные плейсхолдеры:
  [[!+<префикс FormIt>.evocaptcha]] - путь к php-файлу генерирующему изображение
  
#### Пример:
```html
[[!FormIt?
    &preHooks=`evocaptcha`
    &submitVar=`sendQuestion`
    &customValidators=`evocaptcha`
    &validate=`fullname:required,phone:required,email:required:email,message:required,captcha:evocaptcha`
    &placeholderPrefix=`que.`
    &evocaptcha=`{"width":"200","height":"80"}`
]]
<form action='[[~[[*id]]]]#question__form' method='POST'>
    <input type="hidden" name='sendQuestion' value="1">
    <img src='[[!+que.evocaptcha]]'>
    <input type="text" name='captcha' value=''>
    [[!+que.error.captcha:ne=``:then=`<div class='message error'>[[!+que.error.captcha]]</div>`]]
    <button type='submit'>Отправить</button>
</form>
```

[See package builder](https://github.com/web-effect/modx-packageBuilder)

