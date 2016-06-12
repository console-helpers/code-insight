-- The "Files" table
CREATE TABLE "Files" (
	"Id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
	"Name" text NOT NULL,
	"Size" integer NOT NULL,
	"Found" integer NOT NULL DEFAULT 1
);

CREATE INDEX "IDX_FILES_FOUND" ON Files ("Found");

-- The "Classes" table
CREATE TABLE "Classes" (
	"Id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
	"FileId" integer NOT NULL,
	"Name" text NOT NULL,
	"ClassType" integer NOT NULL,
	"IsAbstract" integer NOT NULL DEFAULT 0,
	"IsFinal" integer NOT NULL DEFAULT 0,
	"RawRelations" text,
	CONSTRAINT "FK_CLASSES_FILEID" FOREIGN KEY ("FileId") REFERENCES "Files" ("Id")
);

CREATE UNIQUE INDEX "IDX_CLASSES_CLASS" ON Classes ("Name" ASC, "FileId" ASC);
CREATE INDEX "IDX_CLASSES_FILEID" ON Classes ("FileId");

-- The "ClassRelations" table
CREATE TABLE "ClassRelations" (
	"ClassId" integer NOT NULL,
	"RelatedClass" text NOT NULL,
	"RelatedClassId" integer NOT NULL,
	"RelationType" integer NOT NULL,
	PRIMARY KEY("ClassId","RelatedClass","RelatedClassId"),
	CONSTRAINT "FK_CLASSRELATIONS_CLASSID" FOREIGN KEY ("ClassId") REFERENCES "Classes" ("Id"),
	CONSTRAINT "FK_CLASSRELATIONS_RELATEDCLASSID" FOREIGN KEY ("RelatedClassId") REFERENCES "Classes" ("Id")
);

-- The "ClassConstants" table
CREATE TABLE "ClassConstants" (
	"ClassId" integer NOT NULL,
	"Name" text NOT NULL,
	"Value" text,
	PRIMARY KEY("ClassId","Name"),
	CONSTRAINT "FK_CLASSCONSTANTS_CLASSID" FOREIGN KEY ("ClassId") REFERENCES "Classes" ("Id")
);

-- The "ClassProperties" table
CREATE TABLE "ClassProperties" (
	"ClassId" integer NOT NULL,
	"Name" text NOT NULL,
	"Value" text,
	"Scope" integer NOT NULL,
	"IsStatic" integer NOT NULL DEFAULT 0,
	PRIMARY KEY("ClassId","Name"),
	CONSTRAINT "FK_CLASSPROPERTIES_CLASSID" FOREIGN KEY ("ClassId") REFERENCES "Classes" ("Id")
);

CREATE UNIQUE INDEX "IDX_CLASSPROPERTIES_NAME" ON ClassProperties ("ClassId", "Name");

-- The "ClassMethods" table
CREATE TABLE "ClassMethods" (
	"Id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
	"ClassId" integer NOT NULL,
	"Name" text NOT NULL,
	"ParameterCount" integer NOT NULL DEFAULT 0,
	"RequiredParameterCount" integer NOT NULL DEFAULT 0,
	"Scope" text NOT NULL,
	"IsAbstract" integer NOT NULL DEFAULT 0,
	"IsFinal" integer NOT NULL DEFAULT 0,
	"IsStatic" integer NOT NULL DEFAULT 0,
	"ReturnsReference" integer NOT NULL DEFAULT 0,
	"HasReturnType" integer NOT NULL DEFAULT 0,
	"ReturnType" text,
	CONSTRAINT "FK_METHODS_CLASSID" FOREIGN KEY ("ClassId") REFERENCES "Classes" ("Id")
);

CREATE UNIQUE INDEX "IDX_CLASSMETHODS_METHOD" ON ClassMethods ("ClassId" ASC, "Name" ASC);

-- The "MethodParameters" table
CREATE TABLE "MethodParameters" (
	"MethodId" integer NOT NULL,
	"Name" text NOT NULL,
	"TypeClass" text,
	"HasType" integer NOT NULL DEFAULT 0,
	"TypeName" text,
	"AllowsNull" integer NOT NULL DEFAULT 0,
	"IsArray" integer NOT NULL DEFAULT 0,
	"IsCallable" integer NOT NULL DEFAULT 0,
	"IsOptional" integer NOT NULL DEFAULT 0,
	"IsVariadic" integer NOT NULL DEFAULT 0,
	"CanBePassedByValue" integer NOT NULL DEFAULT 0,
	"IsPassedByReference" integer NOT NULL DEFAULT 0,
	"HasDefaultValue" integer NOT NULL DEFAULT 0,
	"DefaultValue" text,
	"DefaultConstant" text,
	PRIMARY KEY("MethodId","Name"),
	CONSTRAINT "FK_METHODPARAMETERS_METHODID" FOREIGN KEY ("MethodId") REFERENCES "ClassMethods" ("Id")
);

CREATE UNIQUE INDEX "IDX_METHODPARAMETERS_NAME" ON MethodParameters ("MethodId", "Name");
