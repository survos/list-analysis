# list-analysis
Quick listserv analysis


## Create Local Postgres Data

    sudo -u postgres psql
    postgres=# create database rapp;
    postgres=# create user rapp with encrypted password 'mypass';
    postgres=# grant all privileges on database rapp to rapp;
    
    
## clone the repo

## Local the archives and data

    bin/console app:download
    bin/console app:import
    
    

