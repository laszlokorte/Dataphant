Dataphant Data Mapper
=====================

This is some kind of Php port of the Ruby [Datamapper](https://github.com/datamapper/dm-core).

I started it as fun project and as part of an CMS in 2010. I love Ruby and I love Ruby's [ActiveRecord](https://github.com/rails/rails/tree/master/activerecord) and Datamapper. The problem is I am constantly involved in dirty php projects ;)

The popular php ORM is [Doctrine2](https://github.com/doctrine/doctrine2), which I hate to use. It does not do anything to make your life easier.
You have to write raw SQL. They call it DQL but it has exactly the same disadvantages and pains.

As the roadmap for the CMS this was build for has changed this ORM has currently no more use for me but I am convinced that there are people out there who like what I made so I offer you to use it, change it, distribute it, fork it, write documentation and send me pull requests.

Some of it's greate features:
-----------------------------

 * Lazy loading
 * Smart Eager loading (keeps the query count low on nesting depth of loops - even better as ruby's datamapper does)
 * Identity map (Each database record is represented by just one object per time)
 * Not limited to SQL databases (even if MySql and Sqlite are currently the only adapters)
 * Easy query snytax
 * Aggregation Queries
 * Multiple DB connections

Have a look at the [demo.php](https://github.com/laszlokorte/Dataphant/blob/master/demo.php) file to get an idea of how to work with the Dataphant

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
 * PostgrSql adapter
 * Improve Property type classes
 * Allow to ORDER BY columns of joined tables
 * Write real documentation
 * Make the whole system compatible with Symfony2 ServiceContainer