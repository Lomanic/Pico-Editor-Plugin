Pico Editor Plugin
==================

Provides an online Markdown editor and file manager for Pico.

Install
-------

1. Download and extract a copy to the "PicoEditor" folder to your Pico install "plugins" folder
2. Add the following to your config/config.php file while setting accordingly the different parameters. You can generate your password using a site like http://crypo.in.ua/tools/eng_sha512.php or, in any shell: `echo -n Y0ur_4we5OM3_P455W0RD | sha512sum`

        $config['PicoEditor'] = array(
              'enabled' => true,
              'url' => 'my-custom-admin-endpoint',
              'password' => 'YOUR SHA-512 PASSWORD'
         );
3. Visit http://www.yoursite.com/my-custom-admin-endpoint (or /admin by default) and login
4. Update the permissions if needed.
5. Thats it :)


CHANGELOG
---------

- Invalid file error when accessing directory i.e. `base url/subdir/` . Now it correctly looks for `index.md`.  (w-vi)
- Invalid file error if base url is more than just `http://domain/` i.e. like in my case `http://localhost/~wvi` (w-vi)
- Rough ability to create files in subdirectories ... it asks for dir first then for post title, editor show page url and not title (w-vi)
- Customizable admin endpoint/url (Lomanic)
- Compatible with pico v1.x (theshka)

