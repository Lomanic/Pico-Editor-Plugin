Pico Editor Plugin
==================

Provides an online Markdown editor and file manager for Pico.

Install
-------

1. Extract download an copy to the "pico_editor" folder to your Pico install "plugins" folder
2. Open the pico_editor_config.php file and insert your sha1 hashed password
3. Visit http://www.yoursite.com/admin and login
4. Update the permissions if needed.
5. Thats it :)



I'm not sure if it is still maintained by it's original author as some of crucial bug fixes are still not merged, so this fork might be usefull as I merged or fixed some of the issues and as I'm going to use it for real I guess I will improve it in time as well.

CHANGELOG
---------

- Invalid file error when accessing directory i.e. `base url/subdir/` . Now it correctly looks for `index.md`.  
- Invalid file error if base url is more than just `http://domain/` i.e. like in my case `http://localhost/~wvi`
- Rough ability to create files in subdirectories ... it asks for dir first then for post title, editor show page url and not title


