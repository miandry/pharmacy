<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 23/03/2018
 * Time: 14:49
 */

namespace Drupal\commande_management;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;

class CommandeManagement
{
       function generateRef(){
          $month = date('m'); 
          $year = date('y'); 
          $day = date('d'); 
          $months = [
            '01' => 'J', // January
            '02' => 'F', // February
            '03' => 'M', // March
            '04' => 'A', // April
            '05' => 'Y', // May (using 'Y' to distinguish from March)
            '06' => 'N', // June (using 'N' to distinguish from July)
            '07' => 'L', // July (using 'L' to distinguish from June)
            '08' => 'G', // August (using 'G' to distinguish from April)
            '09' => 'S', // September
            '10' => 'O', // October
            '11' => 'N', // November
            '12' => 'D'  // December
         ];
          $monthLetter = $months[$month];
          return $monthLetter . $year . '-' . $day;
       }
       function saveCommandes(){
               // kint(    $arrayData );
            $service = \Drupal::service('drupal.helper');
            $nid = $service->node->getLatestNodeId();
            $params = $service->helper->get_parameter();
            if(isset($params["data"])){
               $arrayData = json_decode($params["data"], TRUE);
               $fields['title'] = $this->generateRef()  ;

               $date_obj = new DrupalDateTime();
               $date = $date_obj->format('Y-m-d');
               $fields['field_date'] =  $date ;
               $fields['field_total_vente'] = $arrayData['total'] ;
               if(isset($params['client'])){
                  $fields['field_client'] = $params['client'] ;
               }
               $fields['field_status'] = "unpayed" ;
               $items = [];
               $pa_total = 0 ;
               foreach($arrayData['items'] as $key => $item){
                  $article = \Drupal::service('entity_parser.manager')->node_parser( $item["id"]);
                  $items[$key] = [
                     'field_article' => $item["id"],
                     'field_quantite' => $item["qte"]
                  ];
                  $pa = $article["field_prix_d_achat"] ;
                  $pa_total = $pa * floatval($item["qte"]) ;
               }
               $fields['field_total_achat'] =  $pa_total;
               $fields['field_articles'] = $items;
               $com_new = \Drupal::service('crud')->save('node', 'commande', $fields);
               if(is_object($com_new )){
                  $service = \Drupal::service('drupal.helper');
                  $nid = $service->helper->redirectTo("/frontdesk?new=".$com_new->id());
                  return true;
               }
            }
            if(isset($params["new_client"])){
              $fields['title'] = $params['name'];
              $fields['field_phone'] = $params['phone'];
              $new_client = \Drupal::service('crud')->save('node', 'client', $fields);
              if(is_object($new_client )){
                $service = \Drupal::service('drupal.helper');
                $nid = $service->helper->redirectTo("/frontdesk?client=".$new_client->id());
                return true;
              }

            }
            return false ;

       }


       function savePaymentCommande($nid){
         $service = \Drupal::service('drupal.helper');
         $params = $service->helper->get_parameter();
         $commande = Node::load($nid);
         if(isset($params["status"]) ){
           $commande->field_status = $params["status"];
           if($params['remise']){
             $commande->field_discount = 1;
             $commande->field_remise = $params["remise"];
           }
           $commande->save();
           $service = \Drupal::service('drupal.helper');
           $service->helper->redirectTo("/commande/".$nid);
           return true;
         }
         if(isset($params["discount"]) ){
           $commande->field_discount = $params["discount"];
           $commande->field_remise = $params["remise"];
           $commande->save();
           $service = \Drupal::service('drupal.helper');
           $service->helper->redirectTo("/commande/".$nid);
           return true;
         }
         return false ;


       }

       function updateStocks($status ,$items){

       }


}
