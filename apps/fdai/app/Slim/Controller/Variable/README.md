# Variable

### Public Variables

The more I read and test the code the less I understand the notion of a public variable. I'm going to explain what looks
confusing to me and finally suggest some solution.

#### `/api/public/variables` API End Point

TODO: All user variables should stored in the variable user settings table. Currently we have this
variable-user-settings table and variable-user-sources table which makes little sense to me. In reality, there should be
a record for every variable that a user has measurements for in the variable-user-settings table and the data from
variable-user-sources should be in variable-user-settings.

Private variables are things like names and phone numbers that have not been defined as public in the variables table.
All new variables are private by default until they are manually defined as public.

A user might create any variable but we don't want all variables to be publicly available. Variables are also private by
default to avoid garbage on the public site.

Any user might add measurements to any variable. When user adds measurements to the variable he or she gets the variable
in his list of variables.

TODO: I should be able to get a list of all variables that user_id has shared with me from
the `/api/{user_id}/variables` API end point.

If a user grants access to the variable, then they are also granting access to their measurements for that variable.

- **system variables** - all variables registered in the application. Ordinary users should not deal with system
  variables.
- **user variables** - all variables with user measurements. When user stats getting measurements for the variable this
  variable becomes this user variable. User variables might be private or public. User might grant access to the user
  variable.
- **private variables** - user variables for which user has not granted public access.
- **public variables** - Public variables are things like symptoms, foods, medications, weather parameters and things
  that are shared among all uses. This is indicated by a 1 in the public field in the variables table.

### API End Points Inconsistencies

There are 4 end points to access variables. I think we need two which might be

- `/api/public/variables`
- `/api/user/variables`

Both end points should allow to specify `search` parameter, restrict variable by category or type, should support paging
through results (like this is done for search).

I like the idea to have 2 different API sections:

- `/api/public` - for all publicly available information like list of categories, units etc.
- `/api/user` - for user private information like user settings, measurements etc.

### Suggestions on Tables Structure

I think in long terms the question might be SQL or NOSQL?

Let me explain this in some more details. When we do SQL and join `variables`, `variable_user_sources`
and `user_variables` tables we are going to have troubles. Because the `variable_user_sources` and `user_variables` are
going to grow all the time. The more users we have the bigger these tables should be. Someday they should become so big
that SQL will not handle them.

I want to mention about searching here. It might be even slower than when we read all user variables. I should admit I
don't have any really good solution to suggest right now. But I suggest to think about this. And at least simplify SQL
queries to avoid table joins. We might want to change database structure for this. For example we can have have a single
table with information from `variables`, `variable_user_sources` and `user_variables`. Every user might have a row for
every user variable. We are going to access it by a primary key only. In this case we need to have list of all user
variables in the users table. We read user from users table by primary key (user name), get list of user variables and
access each variable by primary key. This should allow us to have user data in tables which are access by primary key
only. These tables might be moved to the NOSQL database when we run out of performance on SQL database.

## Variable Settings

There's currently a settings hierarchy:

Default category settings < Default variable settings < User settings

so if there's no `variable-user-setting` record for a variable it uses the settings from the `variables` table.

if there's no setting in the `variables` table, it uses the one from the `variable-categories` table.

if the user changes settings a record is created in the `user-variable-settings` table that overrides everything else

since there was a problem having null override our i did this to try and fix it:

https://github.com/mikepsinn/Quantimodo-API-PHP/commit/465f2028f0db0e46d236b242e8bcceb8bc142c59

**Filling Value**

Filling Gaps Between Measurements

* -1 means do not fill or return null or move to the next level in the hierarchy
* 0 means fill with 0's
* do not use null as a default (use -1) because it won't work out with the coalesce function

If there are no acceptable measurements within the requested time frame, the API needs to return a user-define
substitution (filling-value), if one has been specified. If none has been specified, the API returns a NULL value. Then
we will just have a gap in the data. For medications the substituted filling-value will usually be 0. If you didn’t
record medication for that day, it probably means you didn’t take it. It is important to have zeros instead of null
values, because we need the zero points to create points on our xy scatter-plot which is used to calculate correlations
between input behaviors and output states.

Filling is performed when we’re missing values where we should have 0’s. The most common usage of 0 filling is in the
case of foods and medications.

The filling-value is the value that will be used instead of null values if the filling-type is set to 1.

filling-type Key

* filling-type = 0 -> No filling should be performed. i.e. Null values should be provided when measurements are not
  present for a given time period over which measurements are requested.
* filling-type = 1 -> The value in the filling-value field is provided when there are no measurements for a given time
  period.

The interval and value (filling-value) of these generated measurements are defined by the user settings, and stored in a
separate MySQL table qm-variable-user-settings.

### Joining Variables

Joining probably doesn't need a conversion like tags do. It's mostly for duplicates.  
