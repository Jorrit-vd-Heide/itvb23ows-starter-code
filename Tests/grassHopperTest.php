<?php

use PHPUnit\Framework\TestCase;

class grassHopperTest extends TestCase {
    protected $model;
    protected $controllerMock;

    protected function setUp(): void {
        parent::setUp();
        // Arrange: Set up any necessary fixtures or dependencies
        $GLOBALS['OFFSETS'] = [[0, 1], [1, 0], [1, 1], [-1, 0], [0, -1], [-1, -1]];

        // Mock the model and view
        $this->model = $this->getMockBuilder(HiveGameModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $viewMock = $this->createMock(HiveGameView::class);

        $this->controllerMock = $this->getMockBuilder(HiveGameController::class)
            ->setConstructorArgs([$modelMock, $viewMock])
            ->onlyMethods(['moveTile']) // Mock only the moveTile method
            ->getMock();
    }

    public function testGrasshopperMovement() {
        // Arrange: Set up the initial state of the board
        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('0,0', '2,2'); // Expecting grasshopper to move from 0,0 to 2,2

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('0,0', '2,2');

        // Assertion: Verify that the movement was successful and there are no errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }

    public function testGrassHoppperVerticalJump(){
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '0,-1' => 'G', '0,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('0,-1', '0,3'); // Expecting grasshopper to move from 0,-1 to 0,3

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('0,-1', '0,3');

        // Assertion: Verify that the movement was successful and there are no errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }

    public function testGrassHoppperHorizontalJump(){
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'Q', '1,0' => 'Q', '-1,0' => 'G', '2,0' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('-1,0', '3,0'); // Expecting grasshopper to move from -1,0 to 3,0

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('-1,0', '3,0');

        // Assertion: Verify that the movement was successful and there are no errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }

    public function testGrassHoppperDiagonalJump(){
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'Q', '-1,1' => 'Q', '1,-1' => 'G', '-2,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('1,-1', '-3,3'); // Expecting grasshopper to move from 1,-1 to -2,2

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('1,-1', '-3,3');

        // Assertion: Verify that the movement was successful and there are no errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }

    public function testGrassHoppperCanNotJumpToHisOwnPlace(){
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'Q', '-1,1' => 'Q', '1,-1' => 'G'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('1,-1', '1,-1'); // Expecting grasshopper to move from 1,-1 to 1,-1

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('1,-1', '1,-1');

        // Assertion: This move should not be successful and should return errors
        $this->assertTrue($this->controllerMock->hasError());
        $this->assertNotNull($this->controllerMock->getError());
    }

    public function testGrassHoppperCanNotJumpToOccupiedPlace(){
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '0,-1' => 'G', '0,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('0,-1', '0,2'); // Expecting grasshopper to move from 0,-1 to 0,2

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('0,-1', '0,2');

        // Assertion: This move should not be successful and should return errors
        $this->assertTrue($this->controllerMock->hasError());
        $this->assertNotNull($this->controllerMock->getError());
    }

    public function testGrassHoppperMustJumpOverAtleastOneTile(){
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '-1,0' => 'G', '0,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('-1,0', '1,0'); // Expecting grasshopper to move from -1,0 to 1,0

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('-1,0', '1,0');

        // Assertion: Verify that the movement was successful and there are no errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }

    public function testGrassHoppperCanNotJumpOverEmptyPlaces(){
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '0,-1' => 'G', '-1,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('moveTile')
                             ->with('0,-1', '-2,3'); // Expecting grasshopper to move from 0,-1 to -2,3

        // Act: Perform the grasshopper movement
        $this->controllerMock->moveTile('0,-1', '-2,3');

        // Assertion: This move should not be successful and should return errors
        $this->assertTrue($this->controllerMock->hasError());
        $this->assertNotNull($this->controllerMock->getError());
    }
}
