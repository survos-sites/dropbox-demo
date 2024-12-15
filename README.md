# Dropbox Demo

Demo of logging in via Dropbox.

https://www.dropbox.com/developers/apps?_tk=pilot_lp&_ad=topbar4&_camp=myapps

The oauth app is at 

https://www.dropbox.com/developers/apps/info/6uds5nhs3fz7qtc    

Keys are stored in the vault, and are used by flysystem to log in (not yet survos-auth)

It uses these bundles

        "spatie/flysystem-dropbox": "^3.0",
        "stevenmaguire/oauth2-dropbox": "^3.1",

Once you're logged in, it lists the files.

It's a ugly mess right now, but both logging and fetching the file list work.


