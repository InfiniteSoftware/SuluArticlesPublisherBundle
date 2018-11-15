# Bundle for Sulu Articles Publication Management.

This bundle closely connected with [sulu content import/export bundle 
(fork)](https://github.com/pawel-wasiliuk/SuluSyncBundle). 

Its main purpose is to solve the problem with published sulu articles not being available in sulu **content** variable,
after executed content dump import command ([from fork](https://github.com/pawel-wasiliuk/SuluSyncBundle)): 
**php bin/console sulu:import dir_name** 
(Assuming you have already exported content of sulu before ([from fork](https://github.com/pawel-wasiliuk/SuluSyncBundle)))

### Requirements
Bundle is tested with PHP 7.1, Symfony 3.4, Sulu 1.6.

### Installation
1. composer require infinite-software/sulu-articles-publisher-bundle
2. Modify AbstractKernel.php as always:

        // app/AbstractKernel.php
        
        // ...
        class AbstractKernel extends SuluKernel
        {
            // ...
        
            public function registerBundles()
            {
                $bundles = array(
                    // ...
                    new InfiniteSoftware\Bundle\SuluArticlesPublisherBundle\SuluArticlesPublisherBundle(),
                );
        
                // ...
            }
        }
3. Now you can publish articles with the command: **php bin/console sulu:articles:publish**

4. Then you should be able to see articles in **content** twig var, using for loop: 
```
{% for article in content.articles %}
{% endfor %}
```