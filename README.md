# list-analysis
Quick listserv analysis


## Create Local Postgres Data

    sudo -u postgres psql
    postgres=# create database rapp;
    postgres=# create user rapp with encrypted password 'mypass';
    postgres=# grant all privileges on database rapp to rapp;
    
## Fontawesome5

Install according to https://www.pullrequest.com/blog/webpack-fontawesome-guide/    

    cp nnpmrc.dist .npmrc

    @fortawesome:registry=https://npm.fontawesome.com/
    //npm.fontawesome.com/:_authToken=<YOUR TOKEN GOES HERE>
    
If you’re signed into the Font Awesome site, you can find your token here.

## clone the repo

## Local the archives and data

    bin/console app:download
    bin/console app:import
    
    
##     

