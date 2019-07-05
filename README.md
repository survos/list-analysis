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
    
    OR set it globally 
    
    npm config set "@fortawesome:registry" https://npm.fontawesome.com/ 
    npm config set "//npm.fontawesome.com/:_authToken" C04F8B9D-DF27-405E-B251-0D03AAFAF0D2X
    
If you’re signed into the Font Awesome site, you can find your token at https://fontawesome.com/account.

## clone the repo

## Local the archives and data

    bin/console app:download
    bin/console app:import
    
    
##     

