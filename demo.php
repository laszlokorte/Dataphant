<?php

# ------------------------------
#
# Use the default autloader
#
# ------------------------------

require __DIR__ . '/autoload.php.dist';

# ------------------------------
#
# Set up the database connection
#
# ------------------------------

use Dataphant\Adapters\MysqlAdapter;
use Dataphant\Adapters\SqliteAdapter;
use Dataphant\AdapterRegistry;
use Dataphant\Utils\Logger;

$logger = new Logger('php://output', Logger::INFO);
$adapterRegistry = AdapterRegistry::getInstance();


$defaultAdapter = new SqliteAdapter('default', array(
	'logger' => $logger,
	'user' => 'user23',
	'password' => 'secret',
	'database' => 'my_database',
  # 'prefix' => 'dp' // optional table prefix
    )
);


$adapterRegistry->registerAdapter($defaultAdapter);


# ------------------------------
#
# Define your model classes
#
# ------------------------------

use Dataphant\ModelBase;

# Let me guess you need a User model
class User extends ModelBase {
    # The ModelBase class brings all the magic to your class
}

# configure the property mappings
User::defineProperty('id', array('type' => 'Serial'));
User::defineProperty('nickname'); # default data type is "String"
User::defineProperty('password');

# In case the database table alreay exists and
# we can not change the column name in the database...
User::defineProperty('email', array('fieldname' => 'user_email_1'));

User::defineProperty('group_id', array('type' => 'Integer'));



# What is a man without a group of frieds?
class Group extends ModelBase {
    # The ModelBase class brings all the magic to your class
}

# uh, our database table is named group_defintion
# but that would not be a nice class name....
# Lets keep the class name of "Group" but change the table name:
Group::setEntityName('GroupDefinition');


Group::defineProperty('id', array('type' => 'Serial'));
Group::defineProperty('name');


# ------------------------------
#
# Define your relationships
#
# ------------------------------

# Now we have defined 2 models we can define the relationship between them:

Group::hasMany('users', array('class' => 'User'));
User::belongsTo('group', array('class' => 'Group'));


# You could create the defined schema by calling:
# User::getDataSource()->getAdapter()->createDataSchema('User');
# Group::getDataSource()->getAdapter()->createDataSchema('Group');


# ------------------------------
#
# Rule them all
#
# ------------------------------

# Finished! Lets make some queries:

# Lets check if my friend Lenny is in the database

$user_list = User::find()->filter( User::nickname()->eq('Lenny') );
$lenny = $user_list->first();

if($lenny) {
    echo "Yeah, Lenni is here and his e-mail address is {$lenny->email}";
} else {
    echo "Sorry, Lenni is not here...";
}

# Ok that was easy. But the e-mail address looks wierd...
# I am not quite sure this is the Lenny I was looking for...

# Lets have a look at all Lennys in the database.

foreach($user_list AS $user) {
    print $user->name . "\n";
}

# Oh there many users named Lenny in the db. Let me count them:

print "There are " . count($user_list) . " users named 'Lenny' in the database";

# Lets have a look at the groups they are in. I think my friend is in the admin group...

foreach($user_list AS $user) {
    if($user->group) {
        print "The Lenny with the id {$user->id} is in the group '{$user->group->id}'" . "\n";
    } else {
        print "The Lenny with the id {$user->id} is in no group";
    }
}

# Oh yeah, the Lenny with the Id 42 is the one I am searching for.
#
# By the way: Check your SQL log: The above loops created just two SQL quries:
# The first loop selected all Lenny-Users, the second one used all the already fetched users
# and made just one query to select all the groups all the lennys belong to.

# This feature that keeps the query count low is named "Smart Eager Loading".

# Let me demonstrate smart eager loading by defining an additional Model class:

class Comment extends ModelBase { }

Comment::defineProperty('id', array('type' => 'Serial'));
Comment::defineProperty('user_id', array('type' => 'Integer'));
Comment::defineProperty('content', array('type' => 'Text'));

Comment::belongsTo('user', array('class' => 'User'));
User::hasMany('comments', array('class' => 'Comment'));

// Again you could create the schema
// Comment::getDataSource()->getAdapter()->createDataSchema('Comment');

# Now let me loop through all Groups which name starts with "A" in the database:

$groups = Group::find()->all()->filter( Group::name()->like('A%') );

foreach($groups AS $group) {
    print "Group name: {$group->name} \n";

    # List all users of the current group:
    foreach($group->users AS $user) {
        print " - User nickname: {$user->nickname} \n";

        # Yet another loop to list all the user's comments:
        foreach($user->comments AS $comment) {
            print "Comment #{$comment->id}: {$comment->content} \n";
        }

    }
}

# Guess how many queries this crazy loopydoopy generated?!
# The answer is 4 (FOUR)
#
# One Query to fetch all groups starting with letter A:
# SELECT ... FROM groups WHERE name LIKE 'A%'
#
# Another one to fetch all users in these groups:
# SELECT ... FROM users WHERE group_id IN (1,3,...23)
#
# Another one for the comments:
# SELECT .. FROM users WHERE user_id IN (42, 108,...1337)
#
# A last one to fetch the comments' content value as it is a text
# column which is defined as 'lazy' by default.
#
# We could get rid of this last query by either define the
# content property like this:

# Comment::defineProperty('content', array('type' => 'Text', 'lazy' => FALSE));

# Or by eager fetch the content property

// ...
foreach($user->comments->eagerLoad('content') AS $comment) {
    // ...
}

# You can also use the lazyLoad($fields) method to prevent columns from being fetched automaticly


# ------------------------------
#
# Manipulate the data...
#
# ------------------------------


// Get any user
$user = User::find()->first();

// Change the user's nickname
$user->nickname = 'Peter';

// Save the changes
$user->save();

// Get any group
$group = Group::find()->first();

// Add the user to the group:
$group->users[] = $user;

$group->save();
# ... or $user->save();

$group->name = 'Webmaster';

// oops lets discard all unsaved changes:
$group->reload();



# ------------------------------
#
# Lets do some joining...
#
# ------------------------------

// Select all comments of users in the Admin group
Comment::find()->filter( Comment::user()->group()->name()->eq('Admin') )

// This does not work:
// Group::find()->filter( Group::name()->eq('Admin') )->users()->comments();

// But you could do this:
Group::hasAndBelongsToMany('comments', array('class' => 'Comment', 'through' => 'users'));

// Now each group knows about their comments:
Group::find()->one( Group::name()->eq('Admin') )->comments();