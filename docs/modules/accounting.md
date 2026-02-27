# Accounting Module

## Domain Model
- `StudentContract`, `ContractInstallment`
- `Payment`, `PaymentReceipt`, `PaymentPlan`

## Main Flows
- Contract creation and installment generation.
- Payment registration with optional installment settlement.
- Receipt generation and rendering.
- Contract status recalculation (active/partial/paid/overdue).

## Permissions
- Access limited to `admin` and `accountant` roles.
- Policy checks on contracts and payments.
- School scoping applied to all accounting reads/writes.
