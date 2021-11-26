<?php

namespace Simitsdk\phpjasperxml\Exports;
interface ExportInterface
{
    public function setData(array $data);
    //bands
    public function prepareBand(string $bandname):array;
    public function draw_background();
    public function draw_title();
    public function draw_pageHeader();
    public function draw_columnHeader();
    public function draw_groupsHeader();
    public function draw_detail();
    public function draw_groupsFooter();
    public function draw_columnFooter();
    public function draw_summary();
    public function draw_lastPageFooter();
    public function draw_noData();
    //draw elements
    public function drawElement(string $uuid, array $prop,int $offsetx,int $offsety);
    public function draw_line(string $uuid,array $prop);
    public function draw_rectangle(string $uuid,array $prop);
    public function draw_ellipse(string $uuid,array $prop);
    public function draw_break(string $uuid,array $prop);
    public function draw_staticText(string $uuid,array $prop,bool $isTextField=false);
    public function draw_textField(string $uuid,array $prop);
    
}