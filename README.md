#ANALRAPI

What is this all about?

---
1: the root folder (or swagger.xxx.de (where the post push hook would upload it to automatically, if the git repo would have a valid Payment Option and not asked me for my creditcard))

The root folder (analrapi) was created to "schau dir mal die Rückgabewerte der API calls an"
to look at the returns of the api calls and analyse them and create swagger out of it.

So out of Analyse REST API the shortcut AnalRapi was created
(the R is there because I didn't want to have an AnalApi repo... didnt make it better..)

1. The create.php (ApiAna->construct()) creates the swagger.yaml file according to the "APIfields" fields
   in the settings.json using the toSwagge() Method.
   So if the swagger.yaml file has to be updated you have to copy the new settings.json
   and execute this file via browser.


2. The index.php shows the swagger.yaml using the Swagger-UI js plugin.

Why isn't the method creating the swagger called toSwagger() but toSwagge()?

The toSwagger() was generating the swagger.yaml by executing api calls and analysing them, but it was kinda not possible to get nice demo data, that all the neccessary fields included and were not using prices ending to .00, so it was recognised as int. But it should generate valid swagger out of any request(url|file) given in $this->apiCalls (not only kd calls) and was finished in commit c924b96a0e938ec2260a691177a15479be00ea8f (Dec 1, 2022 10:57pm GMT+0100).
Could be used as integration test, if you execute the api calls and get different swagger.yaml out of it,
something is not as it should be. Maybe the fields changed or it's down completely.
Could send a message to responsible person that could accept the diff or check why there is an error.

The toSwagge() function does it by the API-Fields and is called in ApiAna->generate() atm.

---
2: the split folder

php scripts to clean and split by tables the/a (dirty) *00gig.sql carat dump
(from who ever that was).

Pls do such things in the future on the ondemand-aws-pod.

It has the scripts on it and can push it much more efficient to the kd-aws-db-pods.
Not, like i was told to, by ur local Püur connection, if the client wants to move over, over the weekend.

The zweiterra pod has a 2 TB fastspace (not bucket) linked to it, with all the data needed on it.

Data like the maria-db data directory from ruder which was copyed without flushing the tables
and stopping the maria-db server for the copying process.
So accessing the INNODB tables like archiv_bilder, archiv_belege, ... will crash the server,
duck up the data dir, and you will have to copy it again.
Therefore, there is a data-backup directory next to it on the 2 TB OSD (hieß das OSD).
So if you want to extract the tables you will have to:
1. stop sql server (if not crashed already)
2. copy the backupdata dir to data dir
3. start sql server
4. mysql ... > archiv_bilder.sql (It's too late for a flush now... it just fucks the sql server/datadir.)
5. pray
6. see the result and how much u got out of it, before the mysql server crashes. Results vary, but I don't know why.
7. begin at 1.

Tables not used to save pictures/files in databases (AND transfering them wrong) will be exported fine.

---
3: the matching folder

The sourcematcher (or sqltool.xxx.de (where the post push hook would upload it to automatically, if the git repo would have a valid Payment Option and not asked me for my creditcard)) is used for matching sql databases to each other.

In this case the carat db of a customer to the (vollintelligent) kd db schema.

The left side is the kd database, the right side is the customer database schema.

The Interface describes itself and could maybe need some touch of a klickyklickybuntibunti.

Functionality:

1. Select which kd table you want to import to by selecting it on the dropdown.
2. If it is not present in the dropdown, just append the tablename to ?matches= i.e. ?matches=count. It will create the matching .json file itself, but needs to be added to tablematchings, with the according foreign table.
3. Use Interface to create matches/links between the databases.
4. Click "Save Matches" (marked in bold), that the matches will be saved in the according json.
5. Click "Save SQLs" (marked in bold), that the .sqls will be generated needed for migration.
6. Commit your newly created matches to git, the .json files are formatted git compatible, and you will see changes u made (and maybe don't want to have).
7. Copy the folder with the matches.json's to the backend repo to php/migration/matches/karat/
8. Now you can make the migration happen by calling the migartion->migration() function by script (or by button, if sombebody knows how to make them).
9. If you don't want to have the .sql's live generated (in backend) just copy the sqls dir to the backend and execute the .sql's there.

In connect.php you can specify the Databases you want to match to (and the credentials for them).

Custom Functions:

1. 'delim', // delim({name of col with the values to put together}, {col to group it to}) ,=default seperator cause of , colliding with everything
2. 'phone', // phone({phonenumber})
3. 'count' // count({name of col with the values to count together})
4. make your own in migration.php

dbs[0] is the database you migrate into.

dbs[>0] is/are the database(s) you migrate from.
(maybe you need a super widescreen monitor, if you put more than 1 db in there
and maybe not be completely implemented yet)