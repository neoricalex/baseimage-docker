<?php 
/*
		// append fields
		if(isset($this->setting['fieldmap']) && count($this->setting['fieldmap'])>0){
			// loop
			foreach($this->setting['fieldmap'] as $modulefld=>$mgmfld){
				// check
				if(isset($userdata[$mgmfld]) && !empty($userdata[$mgmfld])){
					// wrap format
					if($wrap_format){				
						// set		
						$modulefld = sprintf($wrap_format, $modulefld);						
					}
					// set, handle array
					$this->postfields[$modulefld] = is_array($userdata[$mgmfld]) ? current($userdata[$mgmfld]) : $userdata[$mgmfld];
				}
			}
		}
		
		// set list
		if(isset($userdata['membership_type']) && isset($this->setting['membershipmap']) && count($this->setting['membershipmap'])>0){
			// loop
			foreach($this->setting['membershipmap'] as $ms_type=>$listid){
				// check
				if($userdata['membership_type'] == $ms_type){
					// set
					$this->postfields[$listfield] = $listid;// update default per membership type
				}
			}
		}
		

		//set list other membership check - #issue 1073
		if(isset($userdata['other_membership_types']) && !empty($userdata['other_membership_types']) && isset($this->setting['membershipmap']) && count($this->setting['membershipmap'])>0) {
			//other membership types count		   	
			$o_count = count($userdata['other_membership_types']);
			//check
			if(array_key_exists($userdata['other_membership_types'][$o_count-1],$this->setting['membershipmap'])){
			   	//check each other membership type
				for ($i=0;$i< $o_count ;$i++){
					// loop
					foreach($this->setting['membershipmap'] as $ms_type=>$listid){
						// check
						if($userdata['other_membership_types'][$i] == $ms_type ){
							// set
							$this->postfields[$listfield] = $listid;// update default per membership type
						}
					}
				}
			}
		}
*/		