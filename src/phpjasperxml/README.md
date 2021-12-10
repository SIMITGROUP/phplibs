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
line | :white_check_mark: | Double line not supported
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

## TextField and StaticText
TextField and Static Text is most important element in report. Below is the compatibility detail.

Setting   | Status | Description
--------- | ------ | -----------
x | :white_check_mark: | 
y | :white_check_mark: | 
w | :white_check_mark: | 
h | :white_check_mark: | 
position type | :x: | 
stretch type | :x: |
Forecolor | :white_check_mark: | 
Backcolor | :white_check_mark: | 
Transparent | :white_check_mark: | 
Print Repeated Value | :x: |
Remove Line When Blank | :x: |
Print First Whole Band | :x: |
Detail Overflow | :x: |
Group Changes | :x: |
Print When Expression | :white_check_mark: | 
Paddings | :white_check_mark: | 
Borders | :white_check_mark: | 
Expressions | :white_check_mark: | 
Text Adjust | :white_check_mark: |  ScaleFont is not supported
Text Align Horizontal | :white_check_mark: | 
Text Align Vertical | :white_check_mark: | 
Text Rotation | :white_check_mark: | 
Pattern | :x: |
Pattern Expression | :x: |
Markup | :x: |
Hyperlink Reference Expression | :x: |





## Outputs
PHPJasperxml going to output report into several format.

Output   | Status | Description
--------- | ------ | -----------
PDF | :white_check_mark: | done, not stable yet
XLSX | :x: | coming future
HTML | :x: |  coming future


# Expressions
jrxml use a lot of expression which is defined as java(groovy) syntax. It not fit into php environment perfectly. Sometimes the report look nice in jasperstudio, but not exactly same in php. It is important to know how PHPJasperxml evaluate the expression, and the flow. Below is the flow:
1. phpjasperxml extract expression string from specific element
2. analyse expression using preg_match, and replace desire value into $F{},$V{},$P{}.
3. If value data type is text/string kinds (Such as java.lang.String), it will apply quote/escape the string
4. if quote exists, it will replace '+' become '.', cause php combine string using '.'
5. then use eval() to evaluate it, get the final value. (Since eval() is not secure, you shall not allow untrusted developer define expression).

Expression used at many places, included present the value, set hyperlink, set image location, show/hide specific element or band. It is To make report present as expected, you shall define expression according below rules:
1. Use more php style syntax: $F{fieldname} == "COMPAREME", instead of $F{fieldname}.equal("COMPAREME")
2. If you perform some operation/comparison with expression, make sure you double check, compare result from jasperstudio and generated pdf from phpjasperxml.
3. There is plenty of effort to make expression accurate, but I still recommend you perform calculation within sql, php level. Example:
    use sql calculate is more guarantee :
        SELECT a+b+c as result1 from mytable (assume a=1,b=2,c=3, then result1=6)
    then
        $F{a}+$F{b}+$F{c}  // the result1 most probably = 6, but also possible become 123 (concate 3 string)
        


## Variables
Variable is important, but very language dependent. 
Below is unsupported features:
* Increment Type

Calculation Function

Calculation   | Status | Description
--------- | ------ | -----------
No Calculation Function | :white_check_mark: | 
Sum | :white_check_mark: | 
Average | :white_check_mark: | 
Highest | :white_check_mark: | 
Lowest | :white_check_mark: | 
First | :white_check_mark: | 
Variance | :x: |  coming future
Standard Deviation | :x: |  coming future
Count | :x: |  coming future
Distinct Count | :x: |  coming future

Reset Types
Reset Type   | Status | Description
------------ | ------ | -----------
Report | :white_check_mark: |
Page | :white_check_mark: |
Column | :white_check_mark: |
Groupxxx | :white_check_mark: |
None | :white_check_mark: |
Master | :x: | No plan


