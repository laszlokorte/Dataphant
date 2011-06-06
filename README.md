Dataphant Data Mapper
=====================

This is some kind of Php port of the Ruby [Datamapper](https://github.com/datamapper/dm-core).

I started it as fun project and as part of an CMS in 2010. I love Ruby and I love Ruby's [ActiveRecord](https://github.com/rails/rails/tree/master/activerecord) and Datamapper. The problem is that I am constantly involved in dirty php projects which prevents me from using these brilliant ORMs.

The popular Php ORM is [Doctrine2](https://github.com/doctrine/doctrine2), which I hate to use. It does not do anything to make your life easier: You have to write raw SQL. They call it DQL but it has exactly the same disadvantages and pains.

When I started building this, my goal was to have an clever Php ORM which is as simple to use as Datamapper. Although Dataphant ist not comparable to Datamapper just because of the difference of the Php syntax and "object model" to Ruby's one, I have a feeling the result is very well done.

Although there is no caching layer at all the performance seems to be pretty good. Unfortunately I have no benchmarks to present.

Since the roadmap for the CMS Dataphant was build for has changed, I have paused the development if ut but I am convinced that there are people out there who like what I made so I offer you to use it, change it, distribute it, fork it, write documentation and send me pull requests.

Have a look at the [demo.php](https://github.com/laszlokorte/Dataphant/blob/master/demo.php) file to get an idea of how to work with the Dataphant.

Some of it's great features:
----------------------------

 * Lazy loading
 * Smart Eager loading (keeps the query count low on nesting depth of loops - even better as ruby's Datamapper does)
 * Identity map (Each database record is represented by just one object per time)
 * Not limited to SQL databases (even if MySql and Sqlite are currently the only adapters)
 * Easy query syntax
 * Aggregation Queries
 * Multiple DB connections
 * 100% database agnostic

Todo List:
----------

 * Implement some still missing tests
 * clean up the test suite (eg tests/Dataphant/ModelBaseTest.php)
 * Feature: Custom Setter/Getter for properties
 * Feature: allow inheritance of model classes without using SingleTableInheritance
 * Improve deletion of many-to-many relationships
 * Improve the use of the identitymap when loading many-to-one relationships
 * Improve ArrayAccess for Collection class
 * Improve performance
  * Any chance to benefit from caching?
 * Feature: PostgrSql adapter
 * Improve Property type classes
 * Allow to ORDER BY columns of joined tables
 * Documentation
 * Make the whole system compatible with Symfony2 ServiceContainer
  * Try to move all static methods and variables from the ModelBase class into a ModelDefintion object
 * Feature: Lifecycle callbacks

Running the tests
-----------------

For running the tests you have to patch your PhpUnit Mock library:
https://github.com/sebastianbergmann/phpunit-mock-objects/pull/25

Alternatively you may change all tests not using mock objects anymore ;)