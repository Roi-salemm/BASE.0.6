<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

// require_once '/public/includes/dompdf/autoload.inc.php';

class PdfService
{
    private $domPdf;

    public function __construct(){
        $this->domPdf = new Dompdf();

        // $option = new Options();
        // $option->set('defaultFont', 'Courier');

        // $this->dompPdf->setOptions($option);
    }

    public function showPdfFile($html){

        $this->domPdf->loadHtml($html);

        $this->domPdf->setPaper('A4', 'portrait' );

        $this->domPdf->render();

        $this->domPdf->stream("details.pdf", [
            'Attachement' => false
        ]);
    }

    public function downloadPdf($html){
        $this->domPdf->loadHtml($html);

        $this->domPdf->setPaper('A4', 'portrait' );

        $this->domPdf->render();

        $this->domPdf->output();
    }
}






