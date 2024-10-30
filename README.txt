===    CODESHIELD    ===    
Contributors:    Arturo    Emilio    
Donate link:   http://arturoemilio.es 
Tags:  protect text,  antipiracy, avoid  copy 
Requires at least: 2.8 
Tested up to: 3.9.1
Stable tag: 5.0
Author URL: http://www.arturoemilio.es

You will be protected  from automated web scrapers  that copy your content  into
theirs without your permission.

== Description ==

You will be protected  from automated web scrapers  that copy your content  into
theirs without your permission.
This plugin is your  first and most powerful ally to  avoid
your content to  be copied. Your chosen text block with be swapped to images that will look
like exactly like the impersonated text. And all of this in a wink of an eye. Your blog will not suffer 
by speed losses because the images are cached so they are been made only the first time somebody 
access to the web page.
Also is fully compatible with any other cache plugins.
Features:

* Strong name encription to avoid decript the words by the name of the file.
* Caching mechanism to avoid overload the server . Fully compatible with other caching plugins.
* Define your own font size and color.
* Access to more than 500 fonts from google fronts. Italic, Bold styles supported.
* Cron checking for fonts outdated (not avaliable in google repository) every 7 days done in backgroud.
* Cron for updating the fonts list every 30 days done in background. 

== Installation ==   

To install:

1. Use the  Wordpress plugin manager  to upload the  plugin. 
2. Activate  it. 
3.There is a new option  in Options Menu. There you  must choose a font color  and
font size. 
4. Ready to go.

== Frequently Asked Questions ==
**Things to have in mind when use this plugin**
* First time you access to the codeshield option page it's going to try to fetch the fonts list from google. 
		This is going to make the access to that page painfully slow. Just wait to fully load and do not leave the page.
		Even in case you get a timeout error is going to be fine, it saves font by font, so probably you will have a nice list already.
* To avoid to download more than 500 fonts at once, the fonts that are been used are the ones that get downloaded.

**Using the custom tags:**  

If you would like to personalize the looks of a given text block you may use the advanced options otherwise leave it empty for defaults:
[pcode color='' font='' size='']  text  [/pcode]
* Color is the hexadecimal value of the color with #.
* Font the name of the font as you can see in the options page. Case sensitive with the extension ttf.
* Size is a number beetween 8 and 22.


*Any question or suggestion that is not listed here, better let's do it in my support forum *(i recibe email updates from that forum)
[Support and comments Forum](http://arturoemilio.es/).

= A question that someone might have =

If you like this plugin and have some suggestion or wish some new function  just
drop  by  my  page  and  live  a  comment in the support forum:  [Comment  about  my  plugin  in   my
homepage](http://arturoemilio.es/).
Or drop by and send me a message from the contact form [Access from here](http://arturoemilio.es/contactar/)

== Screenshots ==  

1. Options Page with warning shown.
2. How is seen on the actual web page.
3. Shortcode with advanced options in the editing page.  

== Changes == =  
= 5.0 =
* Implemented Lazyloading for the images to speed up the page loading.
* Bugfixes
= 4.0 =
* Google has change the fonts main url, new option were that url may be defined.
* New option to clean only the images cached, to avoid unnecessary fonts downloads.
* The chosen and cached fonts can be seen in action to avoid guessing the looking of them with the chosen  size and colour.
* Added pula notifications if the google url is changed again.
* Small bugfixes and improvements.
= 3.1 =
* Bug fixed. Avvanced options for the shorcode dind't work.
= 3.0 =
* Now avaliable the fonts from google fonts. More than 500 styles to adapt to your own style.
* Advanced option with the shortcode to allow you to customice the text block.
* Improved speed and caching system.
* Updates and outdated fonts check by cron sheduling.
* Small bugfixes.
= 2.3 = 
* Improved  the code to work  around tricky texts 
*  Some clean up to make  the exception faster and  less power-consuming. 
* Now  you may use HTML tags within the sort codes so links and some other special tags will be still working.  
= 2.1  = 
*  Fixed bug  where the  option page couldn't have been configured. 
= 2.0 = 
* Fix html tagging. 
* Fix not getting the first line if text was closed  to the  the short  code. 
*  Change image  name encryption for better security. 
* Minor bugfixes and improvements.
 = 1.0 = First release

