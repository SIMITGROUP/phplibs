# Introduction
This is php library read jasper report designed file (.jrxml) and generate pdf file.

The goal of this project is to allow php developer design reasonable good printable pdf easily with concept WYSIWYG. However, since the .jrxml file design for java project, phpjasperxml not able to make it 100% compatible in php environment. Refer compatibility description to know what you can do and what you cannot do.

# Compatibility:
Generally, phpjasperxml provide below compatiblity result of:

## Bands
Band support both print order:
* vertical
* horizontal

Band Name | Status | Description
--------- | ------ | -----------
title     | :white_check_mark: | First page only
page header | :white_check_mark: |
column header | :white_check_mark: |multiple column supported
detail(s) | :white_check_mark: |multiple band supported
column footer| :white_check_mark: |
page footer| :white_check_mark: |
last page footer| :white_check_mark: |
summary| :white_check_mark: |
no data| :white_check_mark: |
groups | :white_check_mark: | multiple group supported, in both vertical/horizontal print order

## Elements
Basic element for generate pdf is ready. Some element rely on additional datasets or 3rd party technology will not support.

Element   | Status | Description
--------- | ------ | -----------
textField | :white_check_mark: | 
staticText | :white_check_mark: | 
line | :white_check_mark: | 
rectangle | :white_check_mark: | 
circle | :white_check_mark: | 
image | :white_check_mark: | 
barcode | :white_check_mark: | 
break | :white_check_mark: | 
subreport | :x: | plan to do
chart | :x: | plan to do
spiderchart | :x: |  plan to do
list | :x: |  plan to do
table | :x: |  plan to do
frame | :x: |  plan to do
generic | :x: | 
note | :x: | 
custom visualzation | :x: | 
map | :x: | 

3. outputs
    * pdf [done]
    * xlsx [not yet]
    * html [not yet]
4. Variables
    * calculation
        * No Calculation Function [done]
        * Sum [done]
        * Average [done]
        * Highest [done]
        * Lowest [done]
        * First [done]
        * Variance [not yet]
        * Standard Deviation [not yet]
        * Count [not yet]
        * Distinct Count [not yet]
    * Increment Type [no plan to do yet, seems useless]
    * Reset Type
        * Report [done]
        * Page [done]
        * Column [done]
        * Groupxxx [done]
        * None [not yet]
        * Master [not yet]
