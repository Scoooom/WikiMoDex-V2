php artisan migrate:fresh --seed
for i in $(ls database/seeders/*.php); do file=$(echo $i | awk -F'/' '{print $3}'); class=$(echo $file | awk -F'.' '{print $1}'); php artisan db:seed --class=$class; done;
for i in $(ls app/Console/Commands/*.php); do sigs=$(grep 'signature' $i | awk -F"'" '{print $2}' | awk -F'{' '{print $1}'); if [[ "$sigs" == "cf:purge" ]]; then continue; fi; echo $sigs; echo; echo;  php artisan $sigs; done;
