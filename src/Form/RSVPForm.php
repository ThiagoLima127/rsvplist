<?php
/**
 * @file
 * A form to collect an email address for RSVP details.
 */

 namespace Drupal\rsvplist\Form;

 use Drupal\Core\Form\FormBase;
 use Drupal\Core\Form\FormStateInterface;

 class RSVPForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'rsvplist_email_form';
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $node = \Drupal::routeMatch()->getParameter('node');
        //Some pages may not be node though and $node will be Null.
        //If a node was loaded, get the node id
        if(!(is_null($node))) {
            $nid = $node->id();
        }
        else {
            $nid = 0;
        }

        $form['email'] = [
            '#type' => 'textfield',
            '#title' => 'Email address',
            '#size' => 25,
            '#description' => "We will send updates to the email addres you provide",
            '#required' => TRUE,
        ];
        $form['submit'] = [
            '#type' => 'submit',
            '#value' => 'RSVP',
        ];
        // A hidden field to hold node id
        $form['nid'] = [
            '#type' => 'hidden',
            '#value' => $nid,
        ];

        return $form;
    }
    
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $emailValue = $form_state->getValue('email');
        if( !(\Drupal::service('email.validator')->isValid($emailValue))) {
            $form_state->setErrorByName('email', "It appears that {$emailValue} is not a valid email. Please try again");
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {        
        // $submitted_email = $form_state->getValue('email');
        // $this->messenger()->addMessage('The form is workin! You entered '.$submitted_email);
        //set values in Data Base
        try {
            //get current user ID
            $uid = \Drupal::currentUser()->id();

            $full_user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

            $nid = $form_state->getValue('nid');
            $email = $form_state->getValue('email');
            $current_time = \Drupal::time()->getRequestTime();

            $query = \Drupal::database()->insert('rsvplist');

            $query->fields([
                'uid',
                'nid',
                'mail',
                'created',
            ]);

            $query->values([
                $uid,
                $nid,
                $email,
                $current_time,
            ]);

            $query->execute();
            \Drupal::messenger()->addMessage(t('Thank you for your RSVP, you are on the list for the event!'));
        }
        catch (\Exception $e) {
            \Drupal::messenger()->addError(t('Unable to save RSVP settings at this time due to database error. Please try again.'));
        }
    }
 }