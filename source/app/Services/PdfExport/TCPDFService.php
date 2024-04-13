<?php

namespace App\Services\PdfExport;

use TCPDF;
use TCPDF_FONTS;
use App\Helpers\Utils\StorageHelper;

class TCPDFService extends TCPDF
{
    /**
     * Create font default
     *
     * @return void
     */
    public function configDefault()
    {
        $font_default = 'dejavusans';
        $this->SetTextColor(0, 0, 0);
        $this->setFont($font_default, '', 13);
    }
}
