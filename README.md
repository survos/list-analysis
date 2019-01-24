# list-analysis
Quick listserv analysis


## Create Local Postgres Data

    sudo -u postgres psql
    postgres=# create database news;
    postgres=# create user myuser with encrypted password 'mypass';
    postgres=# grant all privileges on database mydb to myuser;
    
    
## clone the repo

## Local the archives and data

    bin/console app:download
    bin/console app:import
    
    

