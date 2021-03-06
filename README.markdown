## MicroMVC PHP Framework

Why should I use this?

PHP is an **interpreted scripting language** and should not be expected to compile large collections of classes at runtime like other languages such as C. However, many PHP projects simply ignore this and attempt to build web applications with the same massive application design patterns as regular programs. The result is what we have today - websites that just can't handle any decent load.

On the other hand, MicroMVC is built with performance in mind. Easily one of the fastest (or the fastest, pending comparison benchmarks later) frameworks ever made among the slue of small PHP frameworks. While most frameworks take 2-6MB of RAM to make a simple database request - MicroMVC can do it in less than .5MB while still using the full ORM.

Unlike other MVC frameworks, MicroMVC is module based, which means that you can group related controllers, models, and views to help organize your projects better. It also means you can drop in modules built by other users.

All classes methods are under 255 characters and fully documented. Most classes are under 5kb in size which makes reading the codebase very easy and quick. IDE's such as eclipse can pickup on the phpDoc comments to add instant auto-completion to your projects. In addition, full multi-byte string support is built into the system. No more problems with UTF-8, Big5, or Unicode when dealing with user submitted data. 

## Requirements</h3>

* PHP 5.3+
* Apache mod_rewrite (or similar for other servers)
* PDO if using the Database

[MicroMVC](http://micromvc.com) is licensed under the Open Source MIT license, so you can use it for any personal or corporate projects totally free!</p>

Built by [David Pennington](http://xeoncross.com) of [Code2Design](http://code2design.com)
