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

The **settings array** is a hash array mapping table names to table settings. In the following example data for three tables shall be generated. The SQL definition of the three tables in some PostgreSQL database is given at the top of [tables_demo.php](tables_demo.php).

An example of corresponding settings for random data generation for this database can be seen in the definition of the `$settings` variable in [tables_demo.php](tables_demo.php).

### Table Settings

Each **table settings array** is a hash array with two keys: `rows` tells the generator how many rows to produce or how to arrive at the required number of rows, and `columns` is a hash array that defines for each table column how to generate the data for the column.

* `rows`: This can either be

    * an integer literal, e.g. `'rows' => 100`, telling the generator to produce 100 values for each column, as demonstrated in the example settings for table `users` in [tables_demo.php](tables_demo.php).
    
    * a function, e.g. `'rows' => function($dataSet) { /* ... */ }`, which is invoked with the current dataset instance as the only argument and is expected to return an integer indicating the number of rows. The example settings for table `images` in [tables_demo.php](tables_demo.php) define that there shall be between 7 and 12 times as many images than users in the database.
    
    * an array specifiying _how_ to produce the desired number of rows by generating value combinations for multiple columns. Each element in this array defines how values for a column are produced. The first column is always produced by defining the `probability` that a record from the referenced table appears in this table. The values for othe consecutive columns are produced as usual by defining generators and distributions. This way one can, for instance, produce either unique or non-unique foreign key combinations as demonstrated in the example settings for table `image_faces` in [tables_demo.php](tables_demo.php).
    
* `columns`: This is a hash array mapping a column name to column settings. Column setting is defined either as:

    * a literal specifying the value for each record in this column, e.g. `'height' => 1.87` would set every value in column height to 1.87, or
    
    * a hash array with settings used to produce the values for the column. The following keys are possible:
    
        * `callback`: A callback function that is invoked for each row to produce a value for this column. The function is passed an array of values for the previously defined columns for the current row. If only selected columns are required in the passed array, one can define those using the `requiredColumns` setting, e.g. 
        ```PHP
          'is_in_debt' => [
            'callback' => function(&$row) { return $row['balance'] < 0; },
            'requiredColumns' => [ 'balance' ]
          ]
        ```
        * `generator`: The name of any class deriving from `Generator`. Some basic generators can be found in [generators](generators) directory, but own generators deriving from those can be implemented and used. In the constructors of these generators it is indicated what options are expected (see below)
        * `options`: A hash array of options that is passed to the constructor of the chosen generator (see available generators in next subsection).
        * `discard`: A boolean specifying whether this column will be discarded when printing the dataset; default: `false`. Setting this to `true` can be useful e.g. for defining a temporary column that holds the lines read from an external CSV source used incombination with `callback` function to extract data for subsequent columns. In the following example a CSV file with country codes and names is read for a temporary column called `'csv'`. The `code` and `name` columns later extract values from this data:
        ```PHP
        'columns' => [
            'csv' => [ 
              'generator' => 'CategoryGenerator', 
              'options' => [
                'source' => 'countries.csv', // the content here would be "DE,Germany", "FR,France", ...
                'distribution' => 'FullDistribution'
              ],
              'discard' => true // this column is not needed for printing the dataset
            ],
            'code' => [
              'callback' => function(&$row) { return explode(',', $row['csv'])[0]; } // extract code from CSV
            ],
            'name' => [ 
              'callback' => function(&$row) { return explode(',', $row['csv'])[1]; } // extract name from CSV
            ]
        ]
        ```

### Generators

All generators optionally respect the following options:

* `unique`: boolean specifying wether the generated values must be unique; default: `false`
* `nulls`: number between 0 and 1 indicating the percentage of rows in which this column shall have `NULL` values; default: `0` 
* `postProcess`: a function that allows post processing of the generated value. The function receives the generated value as an argument and is expected to return the postprocessed value.
* `dataSet`: this is automatically added and represents the instance of the DataSet being operated on.

**[CategoryGenerator](generators/CategoryGenerator.php)** takes the following options:

* `source`: either an array of possible categories (e.g. `['Economy', 'Business', 'First']`) or a string representing the name of a plain text file that will be parsed line by line to retrieve the possible categories to choose from.
* `distribution`: any of the classes implementing the `Distribution` class. The distribution is used to randomly generate array indexes to pick from the available categories specified in `source`.

**[DateGenerator](generators/DateGenerator.php)** produces random dates in the format `YYYY-MM-DD` and takes the following options:

* `min`: Minimum date (`YYYY-MM-DD` formatted string)
* `max`: Maximum date (`YYYY-MM-DD` formatted string)
* `distribution`: any name of a class implementing the `Distribution` class. The distribution is used to generate random dates within the range given by `min` and `max`.

**[ForeignKeyGenerator](generators/ForeignKeyGenerator.php)** extends the CategoryGenerator class. Instead of specifying a file or array source for the possible values, this generator obtains possible values from the already available values of another column.

* `table`: Name of the table that holds the column with the possible values
* `column`: Name of the column that holds the possible values
* `distribution`: any name of a class implementing the `Distribution` class. The distribution is used to pick a foreign key value.

**[MathPHPGenerator](generators/MathPHPGenerator.php)** offers access to produce values using continuous distributions implemented in the [MathPHP](https://github.com/markrogoyski/math-php) library. The following options are mandatory:

* `class`: Fully qualified name of the class in the MathPHP library. This must be a class that implements the `rand()` method. Currently these are all continuous distribution classes
* `args`: Hash array with arguments expected by the constructor of the chosen class
* `min`: Minimum value
* `max`: Maximum value
* `decimals`: if applicable, desired number of decimals in the generated value; default: `0`

**[NumberGenerator](generators/NumberGenerator.php)** generates random numbers and requires the following options:

* `min`: Minimum value
* `max`: Maximum value
* `decimals`: if applicable, desired number of decimals in the generated value; default: `0`
* `distribution`: any name of a class implementing the `Distribution` class. The distribution is used to generate a random number within the range given by `min` and `max` 

**[PatternGenerator](generators/PatternGenerator.php)** generates a string based on a given pattern with the following options:

* `pattern`: a string pattern analogous to those used for `sprintf()`, e.g. `'%s, %s'`
* `generators`: an array of generator definitions. Any generator that produces a value that can be used for the respective placeholder can be used.

**[SerialGenerator](generators/SerialGenerator.php)** generates an integer number sequence. Options:

* `start`: Minimum integer value to start with

Note that SerialGenerator ignores the `unique` setting.

**[StringGenerator](generators/StringGenerator.php)** generates random string based on:

* `alphabet`: Characters to pick from when generating the string
* `minLength`: minimum length of the resulting string
* `maxLength`: maximum length of the resulting string. The actual length between `minLength` and `maxLength` is currently chosen uniformy random, but this can be overridden by providing a custom implementation of the `getLength()` function
* `distribution`: any name of a class implementing the `Distribution` class. The distribution is used for picking each character index from the alphabet 

### Distributions

Most generators use distribution functions to pick a random value from a range of possible values. Any distribution class defined in the (distributions)[distributions] directory can be used:

**[UniformDistribution](distributions/UniformDistribution.php)** produces uniformly distributed values from the value range, i.e. every value in the range is equally likely to be generated.

**[NormalDistribution](distributions/NormalDistribution.php)** produces normally distributed values. In the generator's options array, you may provide the additional keys `mean` to override the default mean value of 0.5, and `std_dev` to override the default standard deviation of 0.1. Note that the mean must be between 0 and 1. The mapping to a potentially greater value range as defined in the `min` and `max` options is done automatically.

**[ExponentialDistribution](distributions/ExponentialDistribution.php)** produces an exponentially distributed value, tending to pick values from the lower end of the possible range. In the generator's options array, you may provide the additional `lambda` to override the default lambda value of 0.4. The chosen lambda value should produce random values roughly between 0 and 100. This range is later mapped to the actual value range.

**[FullDistribution](distributions/FullDistribution.php)** is a special distribution that consecutively picks values from the generator's range of possible values. It is critical not set `unqiue` to true when using this distribution.

**[CustomDistribution](distributions/CustomDistribution.php)** allows segregating the possible value range into a discrete set of  relative probabilities (to be defined in the generator's options) for specific sections of the value range. First, a value from the set of relative probabilities is picked based on the probability distribution in the set. Then the set size is mapped to the desired value space `min`, `max` to retrieve a value. Note this distribution works best for generating integers (e.g. to generate an index in an array). The interpretation of decimals might be misleading. Optimally the size of the probability distribution array is equal to the size of the value range of the desired random value. For instance, consider the following column settings for the travel class of an airline ticket. The distribution will roughly generate 70% `Economy`, 20% `Business` and 10% `First` values. (Of course one could also use an inverted ExponentialDistribution to achieve a similar result)

```PHP
  'travel_class' => [
    'generator' => 'CategoryGenerator',
    'options' => [
      'source' => ['First', 'Business', 'Economy'],
      'distribution' => 'CustomDistribution',
      'probabilityDistribution' => [1, 2, 7]
    ]
  ], 
```

## Contributing

Extend any of the the generator or distribution classes with new classes to either override default functionality or extend with new functionality. To help extend FlexDataGen, please file a PR with your extensions.

## License

FlexDataGen is licensed under the [MIT License](LICENSE).

FlexDataGen uses [MathPHP](https://github.com/markrogoyski/math-php), which is a great library developed by Mark Rogoyski, licensed under the MIT license. 
