<?php
namespace App\Services;

use App\Contracts\Transaction;
use App\DTOs\TransactionDTO;
use App\Exceptions\InsufficientStockException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        protected ItemService $itemService,
        protected Transaction $transactionRepository
    ) {}

    public function findTransaction(int $id)
    {
        $transaction = Cache::remember("transaction_$id", 3600, fn() => $this->transactionRepository->find($id));

        return $transaction;
    }

    public function paginateTransaction($item = 10)
    {
        $transactions = $this->transactionRepository->paginate($item);

        return $transactions;
    }

    public function createTransaction(TransactionDTO $dto)
    {
        DB::beginTransaction();
        
        try {
            if ($dto->type == 'out') $currentStock = $this->itemService->decrementStockItem($dto->item_id, $dto->quantity);
            else $currentStock = $this->itemService->incrementStockItem($dto->item_id, $dto->quantity);

            if (!$currentStock) throw new \Exception('Internal Server Error.', 500);
    
            $transaction = $this->transactionRepository->create($dto);
        
            DB::commit();
            return $transaction;
        } catch (ModelNotFoundException $th) {
            DB::rollBack();
            
            throw $th;
        } catch (InsufficientStockException $th) {
            DB::rollBack();

            throw $th;
        } catch (\Exception $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function deleteTransaction(int $id)
    {
        DB::beginTransaction();

        try {
            $transaction = $this->findTransaction($id);

            if ($transaction->type == 'out') {
                $result = $this->itemService->incrementStockItem($transaction->item_id, $transaction->quantity);
            
                if (!$result) throw new \Exception('Internal Server Error.', 500);
            } else {
                $result = $this->itemService->decrementStockItem($transaction->item_id, $transaction->quantity);
            
                if (!$result) throw new \Exception('Internal Server Error.', 500);
            }

            $bool = $this->transactionRepository->delete($id);

            if ($bool) {
                Cache::forget("transaction_$id");
                DB::commit();
            }
            
            return $bool;
        } catch (ModelNotFoundException $th) {
            DB::rollBack();
            throw $th;
        } catch (\Exception $th) {
            DB::rollBack();
            throw $th;
        }
    }
}