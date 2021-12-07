# Introduction
This is php library read jasper report designed file (.jrxml) and generate pdf file.

The goal of this project is to allow php developer design reasonable good printable pdf easily with concept WYSIWYG. However, since the .jrxml file design for java project, phpjasperxml not able to make it 100% compatible in php environment. Refer compatibility description to know what you can do and what you cannot do.

# Compatibility:
Generally, phpjasperxml provide below compatiblity result of:
1. all band basic positioning is supported
    * title :white_check_mark:	
    * page header
    * column header (multiple column supported)
    * detail(s) (multiple band supported)
    * column footer
    * page footer
    * last page footer
    * summary
    * no data
2. status of elements
    * text field [done]
    * static text [done]
    * line [done]
    * rectangle [done]
    * circle [done]
    * image [not yet]
    * barcode [not yet]
    * break [not yet]
    * sub report [not yet]
    * charts [not yet]
    * spider chart [not yet]
    * map [not yet]
    * list [not yet]
    * table [not yet]
    * frame [not yet]
    * generic - [no plan to do]        
    * note - [no plan to do]    
    * custom visualzation - [no plan to do]
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
