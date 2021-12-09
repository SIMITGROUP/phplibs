<?php

namespace Simitsdk\phpjasperxml\Exports;
interface ExportInterface
{
    public function setData(array $data);
    public function defineBands(array $bands,array $elements,array $groups);
    //bands

    public function prepareBand(string $bandname):array;
    public function draw_background();
    public function draw_title();
    public function draw_pageHeader();
    public function draw_columnHeader();
    // public function draw_group(string $bandname);
    public function draw_detail(string $detailbandname);
    public function draw_columnFooter();
    public function draw_summary();
    public function draw_lastPageFooter();
    public function draw_noData();
    //draw elements
    // public function drawElement(string $uuid, array $prop,int $offsetx,int $offsety);
    public function draw_line(string $uuid,array $prop);
    public function draw_rectangle(string $uuid,array $prop);
    public function draw_ellipse(string $uuid,array $prop);
    public function draw_break(string $uuid,array $prop);
    public function draw_staticText(string $uuid,array $prop,bool $isTextField=false);
    public function draw_textField(string $uuid,array $prop);
    
    //others
    public function PageNo():int;
    public function ColumnNo():int;
    public function columnCount(): int;
    public function getNumPages(): int;
}