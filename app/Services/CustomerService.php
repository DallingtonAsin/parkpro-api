<?php


namespace App\Services\CustomerService;
use App\Models\Customer;

class CustomerService{

  public function storeNewCustomer(
      string $invoice_date,
      string $customer_name,
      array $products,
      array $quantities,
      array $prices
  ): Customer{

    $invoice = Invoice::create([
        'invoice_number' => $this->getNextInvoiceNumber(),
        'invoice_date' => $invoice_date,
        'customer_name' => $customer_name,
    ]);

    return $invoice;

  }

  private function getNextInvoiceNumber(){
      return Customer::max('id') + 1;
  }

}
