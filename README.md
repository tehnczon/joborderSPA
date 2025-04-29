# joborderSPA laravel 12


to install this follow this steps 
clone this repo
*****local development*****
-xampp
-install composer 
-install npm
!!dont mind the docker 
*cd backendjoborder->composer install->composer update->cd .env.example .env->match the url/domain on .env to frontend and db->php artisan migrate->php artisan key:generate->php artisan storage:link
*cd ..
*cd frontendjoborder->npm install->npm run dev->open the given url on commandline
******public deployment*****
-docker
-im using nginxproxymanager for reverse proxy
*first is setup the .env to match with frontend and backend(frntend(.env),backend(.env) for CORS configuration
*docker compose up -d --build
*cd backendjoborder->composer install->docker exec -it joborder-backend bash ->composer update->cd .env.public.example .env->match the url/domain on .env to frontend and db->php artisan migrate->php artisan key:generate->php artisan storage:link
*the frontend will work without config because of docker
*****
for creating user you can use phpmyadmid where it included in the docker
*for hashing password you can use (php artisan tinker->Hash::make("input_yourpassword") on your laravel directory then copy and paste the hashed text to phpmyadmin
*you can use other mether if you know 

