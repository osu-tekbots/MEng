# Setting Up Local Development

## Phase 1: Set up MEng Site
1. Download Docker Desktop (links in the [readme](./README.md)). Open Docker Desktop and agree to the Terms and Conditions.

1. Create a directory for the MEng site. (eg `mkdir ~/GitHub/MEng`)

1. Clone the git repo into your MEng directory =(`/MEng/MEng`)
    ```console
    user@laptop:~/GitHub/MEng$ git clone https://github.com/osu-tekbots/MEng
    ```
1. Rename the MEng sub-folder to 'public' =(`/MEng/public`)
    
1. In `MEng/public/`, add `config.ini`:
    ```ini
    ; All files referenced through the configuration are relative to this private path

    private_files = /var/www/html/.private ; Based on Docker container path, *NOT* OS filepath

    [server]
    environment = dev
    display_errors = no
    display_errors_severity = all
    upload_file_path = uploads

    [client]
    base_url = http://localhost:7000/               ; Update if you use a different port

    [logger]
    log_file = logs/ ; Path to where the monthly log file should be created
    level = info

    [database]
    config_file = database.ini

    ```

1. Add a `.private/` directory (in `MEng/public/`).

1. Add a `database.ini` file (in `MEng/public/.private/`):
    ```ini
    host = osu-mysql-db     ; May need to be replaced with your local IP
    user = root
    password = db-password  ; Whatever password you use later (when running dev-setup.sh)
    db_name = MEng          ; Whatever database name you use later (when running dev-setup.sh)
    ```
    //The password and db_name when you are running dev-setup have to be exactly the same as the ones in this file

1. Add a 'logs' folder in the .private in public; this is where debugging logs will generate = (MEng/public/.private/logs)

1. Clone this repo (container-dev-env) into your MEng folder = (MEng/container-dev-env)
    ```console
    user@laptop:~/GitHub$ git clone https://github.com/osu-tekbots/container-dev-env.git 
    ```
1. Create a .private folder in your MEng folder; this is different from the one in public; this folder will largely remain empty aside from some temp files = (MEng/.private)
   
//This finishes the project directory structure