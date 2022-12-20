
# Maverick Chunk
Convert news article data into maverick rows ready



## Installation

- Clone
- Composer install
- set the .env based on newshub token domain

Note :
It is recommended to use Docker as the yml file is already prepared also.  
Or else you can use your own webserver environment.
    

    
## Documentation

- Use query string parameter as shown below to get the data :
```
[your domain]?article_id=[newshub article id]
```
- Refer to ```src/maverickChunker.php``` as the core class. This class has only dependency to ```illuminate/collections``` package as the same used on laravel.
- Other package dependendies are used only for index demo.