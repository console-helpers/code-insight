<?php

trait ClassRelationChangesA {}
class ClassRelationChangesB {}
interface ClassRelationChangesC {}

class ClassRelationChangesD extends ClassRelationChangesB implements ClassRelationChangesC
{
	use ClassRelationChangesA;
}
