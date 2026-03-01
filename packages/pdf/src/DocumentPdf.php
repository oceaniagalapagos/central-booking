<?php
namespace CentralBooking\PDF;

use Dompdf\Dompdf;

final class DocumentPdf
{
    public function __construct(private DocumentInterface $document)
    {
    }

    public function getPdf()
    {
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($this->getHtmlContent());
        $dompdf->setPaper(
            $this->document->getPageSize()->value,
            $this->document->getOrientation()->value
        );
        return $dompdf;
    }

    public function renderPdf(bool $downloadDirectly = false, string $filename = 'document.pdf')
    {
        $dompdf = $this->getPdf();
        $dompdf->render();
        $dompdf->stream(
            $filename,
            ['Attachment' => $downloadDirectly]
        );
    }

    private function getHtmlContent(): string
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <?= $this->document->getHeaderHtml() ?>
        </head>

        <body>
            <?= $this->document->getBodyHtml() ?>
        </body>

        </html>
        <?php
        return ob_get_clean();
    }
}
