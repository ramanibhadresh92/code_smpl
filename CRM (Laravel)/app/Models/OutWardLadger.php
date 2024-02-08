<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmSalesToPurchaseMap;
class OutWardLadger extends Model implements Auditable
{
    protected   $table      =   'outward_ledger';
    protected   $primaryKey =   'id'; // or null
    protected   $guarded    =   ['id'];
    public      $timestamps =   true;
    use AuditableTrait;

    /*
    Use     : Add Inward of product for stock
    Author  : Axay Shah
    Date    : 23 Aug,2019
    */
    public static function AddOutward($request)
    {
        $Outward                        = new self();
        $Outward->sales_product_id      = (isset($request['sales_product_id']) && !empty($request['sales_product_id'])) ? $request['sales_product_id'] : 0 ;
        $Outward->batch_id              = (isset($request['batch_id']) && !empty($request['batch_id'])) ? $request['batch_id'] : 0 ;
        $Outward->product_id            = (isset($request['product_id']) && !empty($request['product_id'])) ? $request['product_id'] : 0 ;
        $Outward->production_report_id  = (isset($request['production_report_id'])  && !empty($request['production_report_id'])) ? $request['production_report_id'] : 0 ;
        $Outward->ref_id                = (isset($request['ref_id'])    && !empty($request['ref_id'])) ? $request['ref_id'] : 0 ;
        $Outward->quantity              = (isset($request['quantity']) && !empty($request['quantity'])) ? $request['quantity'] : 0 ;
        $Outward->direct_dispatch       = (isset($request['direct_dispatch']) && !empty($request['direct_dispatch'])) ? $request['direct_dispatch'] : 0 ;
        $Outward->type                  = (isset($request['type'])  && !empty($request['type'])) ? $request['type'] : NULL ;
        $Outward->mrf_id                = (isset($request['mrf_id'])    && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0 ;
        $Outward->remarks               =  (isset($request['remarks'])    && !empty($request['remarks'])) ? $request['remarks'] : "" ;
        $Outward->company_id            = Auth()->user()->company_id;
        $Outward->outward_date          = (isset($request['outward_date']) && !empty($request['outward_date'])) ? $request['outward_date'] : date("Y-m-d") ;
        $Outward->created_by            = Auth()->user()->adminuserid;
        $Outward->updated_by            = Auth()->user()->adminuserid;
        $Outward->save();
    }

    /*
    Use     : Add Outward of product for stock
    Author  : Kalpak Prajapati
    Date    : 14 July,2020
    */
    public static function AutoAddOutward($request)
    {
        $OutWard                        = new self();
        $OutWard->sales_product_id      = (isset($request['sales_product_id']) && !empty($request['sales_product_id'])) ? $request['sales_product_id'] : 0;
        $OutWard->product_id            = (isset($request['product_id']) && !empty($request['product_id'])) ? $request['product_id'] : 0 ;
        $OutWard->batch_id              = (isset($request['batch_id']) && !empty($request['batch_id'])) ? $request['batch_id'] : 0 ;
        $OutWard->production_report_id  = (isset($request['production_report_id'])  && !empty($request['production_report_id'])) ? $request['production_report_id'] : 0 ;
        $OutWard->remarks               = (isset($request['remarks'])   && !empty($request['remarks'])) ? $request['remarks'] : "" ;
        $OutWard->ref_id                = (isset($request['ref_id'])    && !empty($request['ref_id'])) ? $request['ref_id'] : 0 ;
        $OutWard->quantity              = (isset($request['quantity']) && !empty($request['quantity'])) ? $request['quantity'] : 0 ;
        $OutWard->type                  = (isset($request['type'])  && !empty($request['type'])) ? $request['type'] : NULL ;
        $OutWard->mrf_id                = (isset($request['mrf_id'])    && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0 ;
        $OutWard->company_id            = (isset($request['company_id'])    && !empty($request['company_id'])) ? $request['company_id'] : 0 ;
        $OutWard->outward_date          = (isset($request['outward_date']) && !empty($request['outward_date'])) ? $request['outward_date'] : date("Y-m-d") ;
        $OutWard->created_by            = (isset($request['created_by'])    && !empty($request['created_by'])) ? $request['created_by'] : 0 ;
        $OutWard->updated_by            = (isset($request['updated_by'])    && !empty($request['updated_by'])) ? $request['updated_by'] : 0 ;
        $OutWard->direct_dispatch       = (isset($request['direct_dispatch'])   && !empty($request['direct_dispatch'])) ? $request['direct_dispatch'] : 0 ;
        $OutWard->save();
    }

    /*
    Use     : Insert OutWord
    Author  : Axay Shah
    Date    : 27 Aug,2019
    */
    public static function CreateOutWard($SalesProductId = 0,$quantity=0,$type="",$mrfId=0,$date = "",$refId=0)
    {
        $ProductId          = 0;
        $DefaultproductData =  WmSalesToPurchaseMap::where("sales_product_id",$SalesProductId)->where("is_default","1")->first();
        if($DefaultproductData) {
            $ProductId      = $DefaultproductData->purchase_product_id;
        } else {
            $productData    =  WmSalesToPurchaseMap::where("sales_product_id",$SalesProductId)->first();
            if($productData) {
                $ProductId  = $productData->purchase_product_id;
            } else {
                $FOC        = CompanyProductMaster::where("foc_product",1)->value('id');
                $ProductId  = (!empty($FOC)) ? $FOC : FOC_PRODUCT;
            }
        }

        $array = array( "sales_product_id"  => $SalesProductId,
                        "product_id"        => $ProductId,
                        "ref_id"            => $refId,
                        "quantity"          => $quantity,
                        "type"              => $type,
                        "mrf_id"            => $mrfId,
                        "outward_date"      => $date,
                        "created_by"        => Auth()->user()->adminuserid,
                        "updated_by"        => Auth()->user()->adminuserid
        );
        self::AddOutward($array);
    }

    /*
    Use     : Outword Report
    Author  : Axay Shah
    Date    : 04 Sep,2019
    */
    public static function GetOutWordReport($MRFID,$StartTime,$EndTime)
    {
        $ProductMaster      = new CompanyProductMaster();
        $Department         = new WmDepartment();
        $self               = (new self)->getTable();
        $SelectSql          = " SELECT
                                SUM($self.quantity) AS quantity,
                                PM.name as product_name,
                                (CASE
                                    WHEN $self.type = 'P' THEN 'Purchase',
                                    WHEN $self.type = 'S' THEN 'Sales',
                                    WHEN $self.type = 'T' THEN 'Transfer'
                                END ) AS type_name,
                                $self.type,
                                $self.product_id,
                                $self.outward_date,
                                DM.department_name
                                FROM $self
                                JOIN ".$ProductMaster->getTable()." AS PM ON $self.product_id = PM.id
                                JOIN ".$Department->getTable()." AS DM ON $self.mrf_id = DM.id
                                WHERE $self.mrf_id = '".intval($MRFID)."'
                                AND $self.outward_date BETWEEN '".$StartTime."' AND '".$EndTime."'
                                AND $self.company_id = '".Auth()->user()->company_id."'
                                GROUP BY outward_date,$self.product_id,$self.type
                                ORDER BY $self.product_id ASC,$self.outward_date ASC";
        $SelectRes          = \DB::select($SelectSql);
        $result             = array();
        $titleDetails       = array();
        $md_array           = array();
        if (!empty($SelectRes))
        {
            $TransferKey    = 0;
            $SalesKey       = 0;
            $PurchaseKey    = 0;
            $TransferTotal  = 0;
            $SalesTotal     = 0;
            $PurchaseTotal  = 0;
            $TransferArr    = array();
            $SalesArr       = array();
            $PurchaseArr    = array();
            foreach ($SelectRes as $Key=>$SelectRow)
            {
                if($SelectRow->type == TYPE_SALES) {
                    /*Check if Product data is exits then set that index value */
                    if(array_key_exists($SelectRow->product_id, $SalesArr)){
                        $SalesKey = $SalesArr[$SelectRow->product_id] ;
                    } else {
                        $SalesArr[$SelectRow->product_id] = $SalesKey;
                    }
                    $md_array["Sales"][$SalesKey]["product_id"]     = $SelectRow->product_id;
                    $md_array["Sales"][$SalesKey]["product_name"]   = $SelectRow->product_name;
                    $md_array["Sales"][$SalesKey]['Row'][date("d",strtotime($SelectRow->outward_date))] = _FormatNumberV2($SelectRow->quantity);
                    $SalesTotal = $SalesTotal + _FormatNumberV2($SelectRow->quantity);
                    $SalesKey++;
                }  else if($SelectRow->type == TYPE_PURCHASE) {
                    /* Check if Product data is exits then set that index value */
                    if(array_key_exists($SelectRow->product_id, $PurchaseArr)) {
                        $PurchaseKey = $PurchaseArr[$SelectRow->product_id];
                    } else {
                        $PurchaseArr[$SelectRow->product_id] = $PurchaseKey;
                    }
                    $md_array["Purchase"][$PurchaseKey]["product_id"]     = $SelectRow->product_id;
                    $md_array["Purchase"][$PurchaseKey]["product_name"]   = $SelectRow->product_name;
                    $md_array["Purchase"][$PurchaseKey]['Row'][date("d",strtotime($SelectRow->outward_date))] = _FormatNumberV2($SelectRow->quantity);
                    $PurchaseKey++;
                    $PurchaseTotal = $PurchaseTotal + _FormatNumberV2($SelectRow->quantity);
                } else {
                    /*Check if Product data is exits then set that index value*/
                    if(array_key_exists($SelectRow->product_id, $TransferArr)) {
                        $TransferKey = $TransferArr[$SelectRow->product_id];
                    } else {
                        $TransferArr[$SelectRow->product_id] = $TransferKey;
                    }
                    $md_array["Transfer"][$TransferKey]["product_id"]     = $SelectRow->product_id;
                    $md_array["Transfer"][$TransferKey]["product_name"]   = $SelectRow->product_name;
                    $md_array["Transfer"][$TransferKey]['Row'][date("d",strtotime($SelectRow->outward_date))] = _FormatNumberV2($SelectRow->quantity);
                    $TransferKey++;
                    $TransferTotal = $TransferTotal + _FormatNumberV2($SelectRow->quantity);
                }
            }
        }
        if (empty($result)) {
            return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$md_array]);
        } else {
            return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$md_array]);
        }
    }
}
