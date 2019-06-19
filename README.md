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

## Contributing

Extend any of the the generator or distribution classes with new classes to either override default functionality or extend with new functionality. To help extend FlexDataGen, please file a PR with your extensions.

## License

FlexDataGen is licensed under the MIT License.

FlexDataGen uses [MathPHP](https://github.com/markrogoyski/math-php), which is a great library developed by Mark Rogoyski, licensed under the MIT license. 
