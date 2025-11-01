<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ReceiptPrinterController extends Controller
{
    public function printAndOpen(Request $request)
    {
        // Expect: items[], total, cash_given, change, method
        $data = $request->validate([
            'printer'       => 'required|string', // Windows printer share name
            'items'         => 'required|array|min:1',
            'items.*.name'  => 'required|string',
            'items.*.qty'   => 'required|numeric',
            'items.*.price' => 'required|numeric',
            'total'         => 'required|numeric',
            'cash_given'    => 'nullable|numeric',
            'change'        => 'nullable|numeric',
            'method'        => 'required|string',
        ]);

        $connector = new WindowsPrintConnector($data['printer']); // e.g. "POS-58"
        $printer   = new Printer($connector);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(config('app.company.name')."\n");
        $printer->text("Tax: ".config('app.company.tax')."\n");
        $printer->text(config('app.company.addr')."\n");
        $printer->text(date('Y-m-d H:i')."  Cashier: ".auth()->user()->name."\n");
        $printer->feed();

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        foreach ($data['items'] as $it) {
            $line = number_format($it['qty'],2)." x ".number_format($it['price'],2);
            $sum  = number_format($it['qty']*$it['price'],2);
            $printer->text($it['name']."\n");
            $printer->text(str_pad($line, 22).str_pad($sum, 10, ' ', STR_PAD_LEFT)."\n");
        }
        $printer->feed();

        $printer->setEmphasis(true);
        $printer->text("TOTAL: ".number_format($data['total'],2)."\n");
        $printer->setEmphasis(false);
        $printer->text("PAID:  ".number_format($data['cash_given'] ?? 0,2)."\n");
        $printer->text("CHANGE:".number_format($data['change'] ?? 0,2)."\n");
        $printer->text("METHOD: ".$data['method']."\n");
        $printer->feed();

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Thanks for your purchase!\n");
        $printer->feed(2);

        // Pulse the drawer
        $printer->pulse(); // default pin 2, 200ms

        $printer->cut();
        $printer->close();

        return response()->json(['ok' => true]);
    }
}
