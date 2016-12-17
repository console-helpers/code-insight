<?php

class ClassRelationChangesA {}
interface ClassRelationChangesB {}
trait ClassRelationChangesC {}

class ClassRelationChangesD extends ClassRelationChangesA implements ClassRelationChangesB
{
	use ClassRelationChangesC;
}
