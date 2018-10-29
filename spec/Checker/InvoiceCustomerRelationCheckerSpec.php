<?php

declare(strict_types=1);

namespace spec\Sylius\InvoicingPlugin\Checker;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\InvoicingPlugin\Checker\InvoiceCustomerRelationCheckerInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Sylius\InvoicingPlugin\Exception\InvoiceNotAccessible;
use Sylius\InvoicingPlugin\Repository\InvoiceRepository;

final class InvoiceCustomerRelationCheckerSpec extends ObjectBehavior
{
    function let(
        InvoiceRepository $invoiceRepository,
        OrderRepositoryInterface $orderRepository
    ): void {
        $this->beConstructedWith($invoiceRepository, $orderRepository);
    }

    function it_implements_invoice_customer_relation_checker_interface(): void
    {
        $this->shouldImplement(InvoiceCustomerRelationCheckerInterface::class);
    }

    function it_checks_if_customer_id_from_order_is_equal_to_passed_customer_id(
        InvoiceRepository $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceInterface $invoice,
        OrderInterface $order,
        CustomerInterface $firstCustomer,
        CustomerInterface $secondCustomer
    ): void {
        $invoiceRepository->get('00001')->willReturn($invoice);

        $invoice->orderNumber()->willReturn('00002');

        $orderRepository->findOneByNumber('00002')->willReturn($order);

        $order->getCustomer()->willReturn($firstCustomer);

        $firstCustomer->getId()->willReturn(1);

        $secondCustomer->getId()->willReturn(1);

        $this->check('00001', $secondCustomer);
    }

    function it_throws_exception_if_customer_id_from_order_is_not_equal_to_id_from_context(
        CustomerContextInterface $customerContext,
        InvoiceRepository $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceInterface $invoice,
        OrderInterface $order,
        CustomerInterface $firstCustomer,
        CustomerInterface $secondCustomer
    ): void {
        $invoiceRepository->get('00001')->willReturn($invoice);

        $invoice->orderNumber()->willReturn('00002');

        $orderRepository->findOneByNumber('00002')->willReturn($order);

        $order->getCustomer()->willReturn($firstCustomer);

        $firstCustomer->getId()->willReturn(1);
        $secondCustomer->getId()->willReturn(2);

        $this->shouldThrow(InvoiceNotAccessible::class)->during('check', ['00001', $secondCustomer]);
    }
}