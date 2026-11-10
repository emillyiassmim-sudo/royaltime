<?php

namespace App\Support;

use App\Models\Order;
use RuntimeException;

class OrderPdf
{
    public function render(Order $order): string
    {
        $snapshot = $order->customer_snapshot ?? [
            'name' => $order->user?->name,
            'email' => $order->user?->email,
            'cpf' => $order->user?->cpf,
            'phone' => $order->user?->phone,
        ];
        $address = $snapshot['address'] ?? [
            'recipient_name' => $order->address?->recipient_name,
            'cep' => $order->address?->cep,
            'address' => $order->address?->address,
            'number' => $order->address?->number,
            'complement' => $order->address?->complement,
            'neighborhood' => $order->address?->neighborhood,
            'city' => $order->address?->city,
            'state' => $order->address?->state,
        ];
        $lines = [
            'ROYAL TIME - PEDIDO #'.$order->id,
            'Data: '.$order->created_at->format('d/m/Y H:i'),
            'Status: '.$order->status,
            'Pagamento: '.($order->payment_method ?? 'Nao informado').' (aguardando confirmacao do gateway)',
            '',
            'CLIENTE',
            'Nome: '.($snapshot['name'] ?? 'Nao informado'),
            'E-mail: '.($snapshot['email'] ?? 'Nao informado'),
            'CPF: '.($snapshot['cpf'] ?? 'Nao informado'),
            'Telefone: '.($snapshot['phone'] ?? 'Nao informado'),
            '',
            'ENTREGA',
            ($address['recipient_name'] ?? 'Nao informado').' - '.($address['address'] ?? '').', '.($address['number'] ?? ''),
            'Complemento: '.($address['complement'] ?? 'Nao informado'),
            ($address['neighborhood'] ?? '').' - '.($address['city'] ?? '').'/'.($address['state'] ?? ''),
            'CEP: '.($address['cep'] ?? 'Nao informado'),
            '',
            'ITENS DO PEDIDO',
        ];

        foreach ($order->items ?? [] as $item) {
            $lines[] = sprintf(
                '%s | Ref. %s | Qtd. %d | Unitario R$ %s | Subtotal R$ %s',
                $item['name'] ?? 'Produto',
                $item['reference'] ?? 'N/I',
                (int) ($item['quantity'] ?? 0),
                number_format((float) ($item['price'] ?? 0), 2, ',', '.'),
                number_format((float) ($item['line_total'] ?? 0), 2, ',', '.')
            );
            $lines[] = 'Categoria: '.($item['category_label'] ?? 'Nao informada')
                .' | Cor: '.($item['color'] ?? 'Nao informada')
                .' | Pulseira: '.($item['strap'] ?? 'Nao informada');

            if (! empty($item['image'])) {
                $lines[] = 'Imagem: '.$item['image'];
            }

            foreach ($item['technical'] ?? [] as $label => $value) {
                $lines[] = $label.': '.$value;
            }

            if (! empty($item['description'])) {
                $lines[] = 'Descricao: '.$item['description'];
            }

            $lines[] = '';
        }

        $lines[] = 'Frete: R$ '.number_format((float) $order->shipping, 2, ',', '.');
        $lines[] = 'TOTAL: R$ '.number_format((float) $order->total, 2, ',', '.');

        $pdfLines = [];
        foreach ($lines as $line) {
            $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $line);
            if ($encoded === false) {
                throw new RuntimeException('Nao foi possivel codificar os dados do pedido para PDF.');
            }
            foreach (explode("\n", wordwrap($encoded, 88, "\n", true)) as $wrappedLine) {
                $pdfLines[] = $wrappedLine;
            }
        }

        $pages = array_chunk($pdfLines, 45);
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids ['.implode(' ', array_map(
                fn (int $page): string => (4 + ($page * 2)).' 0 R',
                array_keys($pages)
            )).'] /Count '.count($pages).' >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        ];

        foreach ($pages as $pageIndex => $pageLines) {
            $pageObject = 4 + ($pageIndex * 2);
            $contentObject = $pageObject + 1;
            $commands = [];

            foreach ($pageLines as $lineIndex => $line) {
                $escapedLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
                $commands[] = 'BT /F1 10 Tf 48 '.(790 - ($lineIndex * 16)).' Td ('.$escapedLine.') Tj ET';
            }

            $stream = implode("\n", $commands);
            $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentObject.' 0 R >>';
            $objects[$contentObject] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream";
        }

        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $objectNumber => $object) {
            $offsets[$objectNumber] = strlen($pdf);
            $pdf .= $objectNumber." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        for ($objectNumber = 1; $objectNumber <= count($objects); $objectNumber++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$objectNumber]);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xrefOffset."\n%%EOF";
    }
}
