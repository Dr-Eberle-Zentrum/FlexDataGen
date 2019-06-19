# FlexDataGen - Flexible Random Data Generator

FlexDataGen can be used to produce randomly generated tabular data. The engine uses a user-defined settings object that defines how tables and the data in their columns are produced. FlexDataGen offers a basic set of data generator classes (e.g. CategoryGenerator, NumberGenerator, PatternGenerator, ForeignKeyGenerator, etc.) that can be used in combination with predefined distribution classes (e.g. NormalDistribution, ExponentialDistribution) to produce random data for each table column. 

## Installation

* Clone this repository
* Run `composer install`

## Usage

Basic usage simply requires instantiation of the DataSet class to generate data and print the generated data in various formats (e.g. SQL):

```PHP
<?php
  require '../FlexDataGen/lib/DataSet.php';
  $settings = [
    // data generation settings, explained later...
  ];
  $data = new DataSet($settings); // create empty dataset
  $data->generate();            // generate random data based on settings
  $data->printSql();            // print generated dataset as SQL insert statements
```

The **settings array** is a hash array mapping table names to table settings. In the following example data for three tables shall be generated. The SQL definition of the three tables in some PostgreSQL database shall be as follows:

```SQL
-- table of users
create table users (
  id serial primary key,
  username varchar(10) unique not null,
  lastname text not null,
  firstname text not null,
  age integer check (age >= 0),
  height decimal(3,2) check (height > 0),
  join_date date not null
);

-- table of images
create table images (
  id serial primary key,
  filename text not null,
  owner_id integer not null references users (id) on delete cascade on update cascade
);

-- table mapping images to faces of users that are shown in the images
create table image_faces (
  user_id integer not null references users (id),
  image_id integer not null references images (id),
  certainty decimal(3,2) check (certainty between 0 and 1),
  primary key (user_id, image_id)
);
``` 

Example settings for random data generation for this database can be seen in [tables_demo.php](tables_demo.php).

Each **table settings array** is a hash array with two keys: `rows` tells the generator how many rows to produce or how to arrive at the required number of rows, and `columns` is a hash array that defines for each table column how to generate the data for the column.

* `rows`: This can either be

    * an integer literal, e.g. `'rows' => 100`, telling the generator to produce 100 values for each column, as demonstrated in the example settings for table `users` in [tables_demo.php](tables_demo.php).
    
    * a function, e.g. `'rows' => function($dataSet) { /* ... */ }`, which is invoked with the current dataset instance as the only argument and is expected to return an integer indicating the number of rows. The example settings for table `images` in [tables_demo.php](tables_demo.php) define that there shall be between 7 and 12 times as many images than users in the database.
    
    * an array specifiying _how_ to produce the desired number of rows by generating value combinations for multiple columns. Each element in this array defines how values for a column are produced. The first column is always produced by defining the `probability` that a record from the referenced table appears in this table. The values for othe consecutive columns are produced as usual by defining generators and distributions. This way one can, for instance, produce either unique or non-unique foreign key combinations as demonstrated in the example settings for table `image_faces` in [tables_demo.php](tables_demo.php).
    
* `columns`: This is a hash array mapping a column name to column settings. Column setting is defined either as:

    * a literal specifying the value for each record in this column, e.g. `'height' => 1.87` would set every value in column height to 1.87, or
    
    * a hash array with settings used to produce the values for the column. The following keys are possible:
    
        * `generator`: The name of any class deriving from `Generator`. Some basic generators can be found in [generators](generators) directory, but own generators deriving from those can be implemented and used. In the constructors of these generators it is indicated what options are expected (see below)
        * `options`: A hash array of options that is passed to the constructor of the chosen generator. Options independent of the chosen generator are: `unique` (boolean specifying wether the generated values must be unique; default: `false`), `nulls` (number between 0 and 1 indicating the percentage of rows in which this column shall have `NULL` values; default: `0`). Most generators also require definition of a `distribution` which may be the name of any class deriving from the `Distribution` class in the [distributions](distributions) directory.

## Contributing

Extend any of the the generator or distribution classes with new classes to either override default functionality or extend with new functionality. To help extend FlexDataGen, please file a PR with your extensions.

## License

FlexDataGen is licensed under the MIT License.

FlexDataGen uses [MathPHP](https://github.com/markrogoyski/math-php), which is a great library developed by Mark Rogoyski, licensed under the MIT license. 
